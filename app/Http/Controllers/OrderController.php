<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderStoreRequest;
use App\Http\Requests\OrderUpdateRequest;
use App\Models\BankAccount;
use App\Models\Customer;
use App\Models\DeliveryCharge;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\PurchaseDetail;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
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

        return Inertia::render('backend/orders/Index', [
            'orders' => $orders,
            'bankAccounts' => $this->paymentAccountOptions(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('backend/orders/Create', [
            ...$this->formOptions(),
            'nextOrderNumber' => $this->nextOrderNumber(),
        ]);
    }

    public function edit(Order $order): Response
    {
        abort_if($order->deleted, 404);
        $order->load(['details' => fn ($query) => $query->where('deleted', 0)->orderBy('id')]);

        return Inertia::render('backend/orders/Edit', [
            ...$this->formOptions($order),
            'order' => $this->orderFormData($order),
        ]);
    }

    public function store(OrderStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $userId = $request->user()?->id;

        DB::transaction(function () use ($data, $userId) {
            $items = collect($data['items']);
            $deliveryCharge = DeliveryCharge::query()->findOrFail($data['delivery_charge_id']);
            $subtotal = $items->sum(fn (array $item) => max(
                0,
                ((float) $item['sale_price'] * (int) $item['order_qty']) - (float) $item['discount_amount']
            ));
            $discount = min((float) $data['discount_amount'], $subtotal);
            $total = max(0, $subtotal - $discount + (float) $deliveryCharge->amount);

            $order = new Order;
            $order->order_number = $this->nextOrderNumber();
            $order->customer_id = $data['customer_id'];
            $order->delivery_charge_id = $data['delivery_charge_id'];
            $order->total_product = $items->count();
            $order->subtotal = $subtotal;
            $order->discount_amount = $discount;
            $order->delivery_charge = $deliveryCharge->amount;
            $order->total_amount = $total;
            $order->paid_amount = 0;
            $order->due_amount = $total;
            $order->payment_status = 0;
            $order->order_date = $data['order_date'];
            $order->delivery_date = $data['delivery_date'] ?? null;
            $order->delivery_address = $data['delivery_address'] ?? null;
            $order->note = $data['note'] ?? null;
            $order->order_status = 0;
            $order->created_by = $userId;
            $order->updated_by = $userId;
            $order->save();

            $this->allocateDetails($order, $data['items'], $userId);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Your order has been created and saved successfully.')]);

        return to_route('orders.index');
    }

    public function update(OrderUpdateRequest $request, Order $order): RedirectResponse
    {
        abort_if($order->deleted, 404);

        DB::transaction(function () use ($request, $order) {
            $this->restoreStock($order);
            $order->details()->delete();
            $order->update($this->orderAttributes($order, $request->validated(), $request->user()?->id));
            $this->allocateDetails($order, $request->validated('items'), $request->user()?->id);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Your order has been updated and saved successfully.')]);

        return to_route('orders.index');
    }

    public function advanceStatus(Request $request, Order $order): RedirectResponse
    {
        abort_if($order->deleted, 404);
        abort_unless(in_array($order->order_status, [0, 1], true), 422);

        $nextStatus = $order->order_status + 1;
        $updated = Order::query()
            ->whereKey($order->id)
            ->where('order_status', $order->order_status)
            ->update([
                'order_status' => $nextStatus,
                'updated_by' => $request->user()?->id,
            ]);

        abort_unless($updated, 409);

        $statusLabel = $nextStatus === 1 ? __('Processing') : __('Delivered');
        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Delivery status changed to :status.', ['status' => $statusLabel]),
        ]);

        return back();
    }

    public function payment(Request $request, Order $order): RedirectResponse
    {
        abort_if($order->deleted, 404);

        DB::transaction(function () use ($request, $order) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            $validated = $request->validate([
                'amount' => ['required', 'numeric', 'gt:0'],
                'account_id' => [
                    'required',
                    'integer',
                    Rule::exists('bank_accounts', 'id')->where(fn ($query) => $query->where('deleted', 0)),
                ],
                'note' => ['nullable', 'string', 'max:1000'],
            ]);

            $paymentAmount = (float) $validated['amount'];
            $dueAmount = (float) $order->due_amount;

            if ($this->moneyToCents($paymentAmount) > $this->moneyToCents($dueAmount)) {
                throw ValidationException::withMessages([
                    'amount' => __('Payment amount cannot be greater than the total due amount.'),
                ]);
            }

            $account = BankAccount::query()
                ->whereKey($validated['account_id'])
                ->where('deleted', 0)
                ->lockForUpdate()
                ->firstOrFail();

            $paidAmount = (float) $order->paid_amount + $paymentAmount;
            $remainingDueAmount = max(0, (float) $order->total_amount - $paidAmount);

            $order->update([
                'paid_amount' => $paidAmount,
                'due_amount' => $remainingDueAmount,
                'payment_status' => $remainingDueAmount <= 0 ? 1 : 2,
                'payment_date' => now()->toDateString(),
                'updated_by' => $request->user()?->id,
            ]);

            $account->update([
                'available_balance' => (float) $account->available_balance + $paymentAmount,
                'updated_by' => $request->user()?->id,
            ]);

            Transaction::query()->create([
                'transaction_no' => $this->nextTransactionNumber(),
                'date' => now()->toDateString(),
                'account_id' => $account->id,
                'payment_type' => 1,
                'transaction_type' => 1,
                'reference_type' => Order::class,
                'reference_description' => $order->order_number,
                'description' => 'Order payment for '.$order->order_number,
                'total_amount' => $paymentAmount,
                'notes' => $validated['note'] ?? null,
                'created_by' => $request->user()?->id,
                'updated_by' => $request->user()?->id,
            ]);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Payment recorded.')]);

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

        Inertia::flash('toast', ['type' => 'success', 'message' => __('The order has been deleted successfully.')]);

        return to_route('orders.index');
    }

    private function orderAttributes(Order $order, array $data, ?int $userId): array
    {
        $items = collect($data['items']);
        $deliveryCharge = DeliveryCharge::query()->findOrFail($data['delivery_charge_id']);
        $subtotal = $items->sum(fn (array $item) => max(0, ((float) $item['sale_price'] * (int) $item['order_qty']) - (float) $item['discount_amount']));
        $discount = min((float) $data['discount_amount'], $subtotal);
        $total = max(0, $subtotal - $discount + (float) $deliveryCharge->amount);
        $paid = $order->exists ? min((float) $order->paid_amount, $total) : 0;

        return [
            'order_number' => $order->exists ? $order->order_number : $this->nextOrderNumber(),
            'customer_id' => $data['customer_id'], 'delivery_charge_id' => $data['delivery_charge_id'],
            'total_product' => $items->count(),
            'subtotal' => $subtotal, 'discount_amount' => $discount,
            'delivery_charge' => $deliveryCharge->amount, 'total_amount' => $total,
            'paid_amount' => $paid, 'due_amount' => max(0, $total - $paid),
            'payment_status' => $paid <= 0 ? 0 : ($paid >= $total ? 1 : 2),
            'order_date' => $data['order_date'], 'delivery_date' => $data['delivery_date'] ?? null,
            'delivery_address' => $data['delivery_address'] ?? null, 'note' => $data['note'] ?? null,
            'order_status' => $order->order_status, 'updated_by' => $userId,
        ];
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

                $orderDetail = new OrderDetail;
                $orderDetail->order_detail_number = $order->order_number.'-'.str_pad((string) $line++, 3, '0', STR_PAD_LEFT);
                $orderDetail->order_id = $order->id;
                $orderDetail->product_id = $item['product_id'];
                $orderDetail->purchase_detail_id = $batch->id;
                $orderDetail->regular_price = $item['regular_price'];
                $orderDetail->sale_price = $item['sale_price'];
                $orderDetail->order_qty = $quantity;
                $orderDetail->discount_amount = $discount;
                $orderDetail->total_amount = max(0, (float) $item['sale_price'] * $quantity - $discount);
                $orderDetail->status = 1;
                $orderDetail->created_by = $userId;
                $orderDetail->updated_by = $userId;
                $orderDetail->save();

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

    private function nextTransactionNumber(): string
    {
        $lastNumber = Transaction::query()
            ->where('transaction_no', 'like', 'TRX-%')
            ->pluck('transaction_no')
            ->map(fn (string $number): ?int => preg_match('/^TRX-(\d+)$/', $number, $matches) ? (int) $matches[1] : null)
            ->filter()
            ->max();

        return 'TRX-'.(max(1000, $lastNumber ?? 0) + 1);
    }

    private function moneyToCents(float $amount): int
    {
        return (int) round($amount * 100);
    }

    private function paymentAccountOptions(): array
    {
        return BankAccount::query()
            ->where('deleted', 0)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get(['id', 'name', 'account_number', 'available_balance', 'is_default'])
            ->map(fn (BankAccount $account) => [
                'id' => $account->id,
                'name' => $account->name,
                'account_number' => $account->account_number,
                'available_balance' => $account->available_balance,
                'is_default' => $account->is_default,
            ])
            ->all();
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
                ->orderBy('name')->get(['id', 'name', 'sku', 'thumbnail', 'unit_id', 'cost_price', 'sale_price', 'stock_quantity'])
                ->map(fn (Product $product) => [
                    'id' => $product->id, 'name' => $product->name, 'sku' => $product->sku,
                    'thumbnail_url' => $product->thumbnail ? Storage::disk('public')->url($product->thumbnail) : null,
                    'unit' => $product->measurementUnit?->short_name, 'regular_price' => $product->cost_price,
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
