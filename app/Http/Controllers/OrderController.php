<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderStoreRequest;
use App\Http\Requests\OrderUpdateRequest;
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
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(): Response
    {
        $orders = Order::query()
            ->with(['customer:id,name,phone,profile_image', 'creator:id,name', 'details.product:id,name,sku'])
            ->where('deleted', 0)
            ->latest('id')
            ->get()
            ->map(fn (Order $order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer?->name,
                'customer_phone' => $order->customer?->phone,
                'customer_photo_url' => $order->customer?->profile_image
                    ? Storage::disk('public')->url($order->customer->profile_image) : null,
                'total_product' => $order->total_product,
                'subtotal' => $order->subtotal,
                'discount_amount' => $order->discount_amount,
                'delivery_charge' => $order->delivery_charge,
                'total_amount' => $order->total_amount,
                'paid_amount' => $order->paid_amount,
                'due_amount' => $order->due_amount,
                'payment_status' => $order->payment_status,
                'order_status' => $order->order_status,
                'order_date' => $order->order_date?->format('Y-m-d'),
                'delivery_date' => $order->delivery_date?->format('Y-m-d'),
                'created_by_name' => $order->creator?->name,
                'details' => $order->details->where('deleted', 0)->values()->map(fn (OrderDetail $detail) => [
                    'id' => $detail->id,
                    'product_name' => $detail->product?->name ?? 'Unknown product',
                    'product_sku' => $detail->product?->sku,
                    'order_qty' => $detail->order_qty,
                    'sale_price' => $detail->sale_price,
                    'discount_amount' => $detail->discount_amount,
                    'total_amount' => $detail->total_amount,
                ]),
            ]);

        return Inertia::render('orders/Index', ['orders' => $orders]);
    }

    public function create(): Response
    {
        return Inertia::render('orders/Create', [
            ...$this->formOptions(),
            'nextOrderNumber' => $this->nextOrderNumber(),
        ]);
    }

    public function edit(Order $order): Response
    {
        abort_if($order->deleted, 404);
        $order->load(['details' => fn ($query) => $query->where('deleted', 0)->orderBy('id')]);

        return Inertia::render('orders/Edit', [
            ...$this->formOptions($order),
            'order' => $this->orderFormData($order),
        ]);
    }

    public function store(OrderStoreRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $order = $this->saveOrder(new Order, $request->validated(), $request->user()?->id);
            $this->allocateDetails($order, $request->validated('items'), $request->user()?->id);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Order created.')]);

        return to_route('orders.index');
    }

    public function update(OrderUpdateRequest $request, Order $order): RedirectResponse
    {
        abort_if($order->deleted, 404);

        DB::transaction(function () use ($request, $order) {
            $this->restoreStock($order);
            $order->details()->delete();
            $this->saveOrder($order, $request->validated(), $request->user()?->id);
            $this->allocateDetails($order, $request->validated('items'), $request->user()?->id);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Order updated.')]);

        return to_route('orders.index');
    }

    public function destroy(Request $request, Order $order): RedirectResponse
    {
        abort_if($order->deleted, 404);

        DB::transaction(function () use ($request, $order) {
            $this->restoreStock($order);
            $order->details()->update(['deleted' => 1, 'deleted_at' => now(), 'deleted_by' => $request->user()?->id]);
            $order->update(['deleted' => 1, 'deleted_at' => now(), 'deleted_by' => $request->user()?->id]);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Order deleted.')]);

        return to_route('orders.index');
    }

    private function saveOrder(Order $order, array $data, ?int $userId): Order
    {
        $items = collect($data['items']);
        $deliveryCharge = DeliveryCharge::query()->findOrFail($data['delivery_charge_id']);
        $subtotal = $items->sum(fn (array $item) => max(0, ((float) $item['sale_price'] * (int) $item['order_qty']) - (float) $item['discount_amount']));
        $discount = min((float) $data['discount_amount'], $subtotal);
        $total = max(0, $subtotal - $discount + (float) $deliveryCharge->amount);
        $paid = $order->exists ? min((float) $order->paid_amount, $total) : 0;

        $order->fill([
            'order_number' => $order->exists ? $order->order_number : $this->nextOrderNumber(),
            'customer_id' => $data['customer_id'], 'delivery_charge_id' => $data['delivery_charge_id'],
            'total_product' => $items->count(),
            'subtotal' => $subtotal, 'discount_amount' => $discount,
            'delivery_charge' => $deliveryCharge->amount, 'total_amount' => $total,
            'paid_amount' => $paid, 'due_amount' => max(0, $total - $paid),
            'payment_status' => $paid <= 0 ? 0 : ($paid >= $total ? 1 : 2),
            'order_date' => $data['order_date'], 'delivery_date' => $data['delivery_date'] ?? null,
            'delivery_address' => $data['delivery_address'] ?? null, 'note' => $data['note'] ?? null,
            'order_status' => 0, 'updated_by' => $userId,
        ]);
        if (! $order->exists) {
            $order->created_by = $userId;
        }
        $order->save();

        return $order;
    }

    private function allocateDetails(Order $order, array $items, ?int $userId): void
    {
        $line = 1;
        foreach ($items as $itemIndex => $item) {
            $remaining = (int) $item['order_qty'];
            $batches = PurchaseDetail::query()->where('product_id', $item['product_id'])
                ->where('deleted', 0)->where('receive_status', 1)->where('available_qty', '>', 0)
                ->orderByRaw('expire_date is null')->orderBy('expire_date')->orderBy('id')->lockForUpdate()->get();

            if ($batches->sum('available_qty') < $remaining) {
                throw ValidationException::withMessages(["items.$itemIndex.order_qty" => __('Only :qty item(s) are available in stock.', ['qty' => $batches->sum('available_qty')])]);
            }

            $unitDiscount = (float) $item['discount_amount'] / $remaining;
            foreach ($batches as $batch) {
                if ($remaining === 0) {
                    break;
                }
                $quantity = min($remaining, (int) $batch->available_qty);
                $discount = round($unitDiscount * $quantity, 2);
                $order->details()->create([
                    'order_detail_number' => $order->order_number.'-'.str_pad((string) $line++, 3, '0', STR_PAD_LEFT),
                    'product_id' => $item['product_id'], 'purchase_detail_id' => $batch->id,
                    'regular_price' => $item['regular_price'], 'sale_price' => $item['sale_price'],
                    'order_qty' => $quantity, 'discount_amount' => $discount,
                    'total_amount' => max(0, (float) $item['sale_price'] * $quantity - $discount),
                    'status' => 1, 'created_by' => $userId, 'updated_by' => $userId,
                ]);
                $batch->decrement('available_qty', $quantity);
                $batch->increment('sell_qty', $quantity);
                $remaining -= $quantity;
            }

            Product::query()->whereKey($item['product_id'])->decrement('stock_quantity', (int) $item['order_qty']);
        }
    }

    private function restoreStock(Order $order): void
    {
        $order->details()->where('deleted', 0)->lockForUpdate()->get()->each(function (OrderDetail $detail) {
            if ($detail->purchase_detail_id) {
                $batch = PurchaseDetail::query()->lockForUpdate()->find($detail->purchase_detail_id);
                if ($batch) {
                    $batch->increment('available_qty', $detail->order_qty);
                    $batch->update(['sell_qty' => max(0, $batch->sell_qty - $detail->order_qty)]);
                }
            }
            Product::query()->whereKey($detail->product_id)->increment('stock_quantity', $detail->order_qty);
        });
    }

    private function nextOrderNumber(): string
    {
        $last = Order::query()->where('order_number', 'like', 'ORD-%')->pluck('order_number')
            ->map(fn (string $number) => preg_match('/^ORD-(\d+)$/', $number, $matches) ? (int) $matches[1] : null)->filter()->max();

        return 'ORD-'.(max(1000, $last ?? 0) + 1);
    }

    private function formOptions(?Order $editingOrder = null): array
    {
        $reserved = $editingOrder?->details->where('deleted', 0)->groupBy('product_id')->map->sum('order_qty') ?? collect();

        return [
            'customers' => Customer::query()->where('deleted', 0)->where('status', 1)->orderBy('name')
                ->get(['id', 'name', 'profile_image', 'email', 'phone', 'address'])->map(fn (Customer $customer) => [
                    'id' => $customer->id, 'name' => $customer->name,
                    'photo_url' => $customer->profile_image ? Storage::disk('public')->url($customer->profile_image) : null,
                    'email' => $customer->email, 'phone' => $customer->phone, 'address' => $customer->address,
                ]),
            'products' => Product::query()->with('measurementUnit:id,short_name')->where('deleted', 0)->where('status', 1)
                ->orderBy('name')->get(['id', 'name', 'sku', 'thumbnail', 'unit_id', 'regular_price', 'sale_price', 'stock_quantity'])
                ->map(fn (Product $product) => [
                    'id' => $product->id, 'name' => $product->name, 'sku' => $product->sku,
                    'thumbnail_url' => $product->thumbnail ? Storage::disk('public')->url($product->thumbnail) : null,
                    'unit' => $product->measurementUnit?->short_name, 'regular_price' => $product->regular_price,
                    'sale_price' => $product->sale_price, 'stock_quantity' => $product->stock_quantity + ($reserved[$product->id] ?? 0),
                ]),
            'deliveryCharges' => DeliveryCharge::query()->where('deleted', 0)->where('status', 1)
                ->orderBy('title')->get(['id', 'title', 'amount'])
                ->map(fn (DeliveryCharge $deliveryCharge) => [
                    'id' => $deliveryCharge->id,
                    'title' => $deliveryCharge->title,
                    'amount' => $deliveryCharge->amount,
                ]),
        ];
    }

    private function orderFormData(Order $order): array
    {
        return [
            'id' => $order->id, 'order_number' => $order->order_number, 'customer_id' => $order->customer_id,
            'delivery_charge_id' => $order->delivery_charge_id,
            'order_date' => $order->order_date?->format('Y-m-d'), 'delivery_date' => $order->delivery_date?->format('Y-m-d'),
            'delivery_address' => $order->delivery_address, 'note' => $order->note,
            'discount_amount' => $order->discount_amount, 'delivery_charge' => $order->delivery_charge,
            'items' => $order->details->groupBy('product_id')->map(function ($details) {
                $first = $details->first();

                return ['product_id' => $first->product_id, 'order_qty' => $details->sum('order_qty'),
                    'regular_price' => $first->regular_price, 'sale_price' => $first->sale_price,
                    'discount_amount' => $details->sum('discount_amount')];
            })->values(),
        ];
    }
}
