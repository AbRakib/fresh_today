<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Customer;
use App\Models\DeliveryCharge;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\PurchaseDetail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function show(Request $request): Response|RedirectResponse
    {
        $customerId = $request->session()->get('customer_id');

        if (! $customerId) {
            return to_route('home')->with('customer_auth_modal', 'login');
        }

        $cartItems = $this->cartItems($customerId);

        if ($cartItems->isEmpty()) {
            Inertia::flash('toast', ['type' => 'error', 'message' => __('Your cart is empty.')]);

            return to_route('cart.index');
        }

        return Inertia::render('frontend/Checkout', [
            'cart_products' => $cartItems->map(fn (Cart $cart) => $this->cartProductData($cart)),
            'delivery_charges' => DeliveryCharge::query()
                ->where('deleted', 0)
                ->where('status', 1)
                ->orderBy('title')
                ->get(['id', 'title', 'amount'])
                ->map(fn (DeliveryCharge $charge) => [
                    'id' => $charge->id,
                    'title' => $charge->title,
                    'amount' => $charge->amount,
                ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $customerId = $request->session()->get('customer_id');

        if (! $customerId) {
            return to_route('home')->with('customer_auth_modal', 'login');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'delivery_charge_id' => ['required', 'integer', Rule::exists('delivery_charges', 'id')->where('deleted', 0)->where('status', 1)],
            'delivery_address' => ['required', 'string', 'max:2000'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($customerId, $data) {
            Customer::query()->whereKey($customerId)->update([
                'name' => $data['name'],
                'phone' => $data['phone'],
                'address' => $data['delivery_address'],
            ]);

            $cartItems = $this->cartItems($customerId, true);

            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => __('Your cart is empty.'),
                ]);
            }

            $deliveryCharge = DeliveryCharge::query()
                ->whereKey($data['delivery_charge_id'])
                ->where('deleted', 0)
                ->where('status', 1)
                ->lockForUpdate()
                ->firstOrFail();

            $items = $cartItems->map(function (Cart $cart) {
                $product = $cart->product;
                $salePrice = $product->sale_price ?? $product->cost_price;

                return [
                    'product_id' => $product->id,
                    'order_qty' => $cart->quantity,
                    'regular_price' => $product->cost_price,
                    'sale_price' => $salePrice,
                    'discount_amount' => 0,
                ];
            })->all();

            $subtotal = collect($items)->sum(fn (array $item) => (float) $item['sale_price'] * (int) $item['order_qty']);
            $total = $subtotal + (float) $deliveryCharge->amount;

            $order = Order::query()->create([
                'order_number' => $this->nextOrderNumber(),
                'customer_id' => $customerId,
                'delivery_charge_id' => $deliveryCharge->id,
                'total_product' => count($items),
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'delivery_charge' => $deliveryCharge->amount,
                'total_amount' => $total,
                'paid_amount' => 0,
                'due_amount' => $total,
                'payment_status' => 0,
                'order_date' => now()->toDateString(),
                'delivery_address' => $data['delivery_address'],
                'note' => $data['note'] ?? null,
                'order_status' => 0,
            ]);

            $this->allocateDetails($order, $items);

            Cart::query()
                ->where('customer_id', $customerId)
                ->delete();
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Your order has been placed successfully.')]);

        return to_route('shop');
    }

    private function cartItems(int $customerId, bool $lock = false)
    {
        $query = Cart::query()
            ->where('customer_id', $customerId)
            ->whereHas('product', fn ($query) => $query
                ->where('deleted', 0)
                ->where('status', 1))
            ->with(['product.category:id,name', 'product.measurementUnit:id,short_name'])
            ->oldest();

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->get();
    }

    private function cartProductData(Cart $cart): array
    {
        $product = $cart->product;
        $price = $product->sale_price ?? $product->cost_price;

        return [
            'id' => $product->id,
            'category_name' => $product->category?->name,
            'name' => $product->name,
            'slug' => $product->slug,
            'thumbnail_url' => $product->thumbnail
                ? Storage::disk('public')->url($product->thumbnail)
                : null,
            'unit' => $product->measurementUnit?->short_name,
            'gross_weight' => $product->gross_weight,
            'weight' => $product->weight,
            'regular_price' => $product->cost_price,
            'sale_price' => $product->sale_price,
            'quantity' => $cart->quantity,
            'line_total' => number_format((float) $price * $cart->quantity, 2, '.', ''),
        ];
    }

    private function allocateDetails(Order $order, array $items): void
    {
        $line = 1;

        foreach ($items as $itemIndex => $item) {
            $remaining = (int) $item['order_qty'];
            $batches = PurchaseDetail::query()
                ->where('product_id', $item['product_id'])
                ->where('deleted', 0)
                ->where('receive_status', 1)
                ->where('available_qty', '>', 0)
                ->orderByRaw('expire_date is null')
                ->orderBy('expire_date')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($batches->sum('available_qty') < $remaining) {
                throw ValidationException::withMessages([
                    "items.$itemIndex.order_qty" => __('Only :qty item(s) are available in stock.', ['qty' => $batches->sum('available_qty')]),
                ]);
            }

            foreach ($batches as $batch) {
                if ($remaining === 0) {
                    break;
                }

                $quantity = min($remaining, (int) $batch->available_qty);

                OrderDetail::query()->create([
                    'order_detail_number' => $order->order_number.'-'.str_pad((string) $line++, 3, '0', STR_PAD_LEFT),
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'purchase_detail_id' => $batch->id,
                    'regular_price' => $item['regular_price'],
                    'sale_price' => $item['sale_price'],
                    'order_qty' => $quantity,
                    'discount_amount' => 0,
                    'total_amount' => (float) $item['sale_price'] * $quantity,
                    'status' => 1,
                ]);

                $batch->decrement('available_qty', $quantity);
                $batch->increment('sell_qty', $quantity);
                Product::query()->whereKey($item['product_id'])->decrement('stock_quantity', $quantity);
                $remaining -= $quantity;
            }
        }
    }

    private function nextOrderNumber(): string
    {
        $last = Order::query()
            ->where('order_number', 'like', 'ORD-%')
            ->pluck('order_number')
            ->map(fn (string $number) => preg_match('/^ORD-(\d+)$/', $number, $matches) ? (int) $matches[1] : null)
            ->filter()
            ->max();

        return 'ORD-'.(max(1000, $last ?? 0) + 1);
    }
}
