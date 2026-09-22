<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseStoreRequest;
use App\Http\Requests\PurchaseUpdateRequest;
use App\Models\BankAccount;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Supplier;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PurchaseController extends Controller
{
    public function index(): Response
    {
        $purchases = Purchase::query()
            ->with([
                'supplier:id,name,phone,photo',
                'creator:id,name',
                'details' => fn ($query) => $query
                    ->where('deleted', 0)
                    ->orderBy('id')
                    ->with('product:id,name,sku'),
            ])
            ->where('deleted', 0)
            ->latest('id')
            ->get()
            ->map(fn (Purchase $purchase) => [
                'id' => $purchase->id,
                'purchase_number' => $purchase->purchase_number,
                'supplier_name' => $purchase->supplier?->name,
                'supplier_photo_url' => $purchase->supplier?->photo
                    ? Storage::disk('public')->url($purchase->supplier->photo)
                    : null,
                'supplier_phone' => $purchase->supplier?->phone,
                'total_product' => $purchase->total_product,
                'subtotal' => $purchase->subtotal,
                'paid_amount' => $purchase->paid_amount,
                'due_amount' => $purchase->due_amount,
                'purchase_date' => $purchase->purchase_date?->format('Y-m-d'),
                'created_by_name' => $purchase->creator?->name,
                'note' => $purchase->note,
                'receive_status' => $purchase->receive_status,
                'payment_status' => $purchase->payment_status,
                'details' => $purchase->details->map(fn (PurchaseDetail $detail) => [
                    'id' => $detail->id,
                    'product_name' => $detail->product?->name ?? 'Unknown product',
                    'product_sku' => $detail->product?->sku,
                    'purchase_qty' => $detail->purchase_qty,
                    'purchase_price' => $detail->purchase_price,
                    'total_amount' => $detail->total_amount,
                    'expire_date' => $detail->expire_date?->format('Y-m-d'),
                ]),
            ]);

        return Inertia::render('purchases/Index', [
            'purchases' => $purchases,
            'bankAccounts' => $this->paymentAccountOptions(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('purchases/Create', [
            ...$this->formOptions(),
            'nextPurchaseNumber' => $this->nextPurchaseNumber(),
        ]);
    }

    public function edit(Purchase $purchase): Response
    {
        abort_if($purchase->deleted, 404);

        $purchase->load(['details' => fn ($query) => $query->where('deleted', 0)->orderBy('id')]);

        return Inertia::render('purchases/Edit', [
            ...$this->formOptions(),
            'purchase' => $this->purchaseFormData($purchase),
        ]);
    }

    public function store(PurchaseStoreRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $purchase = $this->savePurchase(new Purchase, $request->validated(), $request->user()?->id);
            $this->createDetails($purchase, $request->validated('items'), $request->user()?->id);
            $this->adjustSupplierBalance($purchase->supplier_id, -(float) $purchase->due_amount);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Purchase created.')]);

        return to_route('purchases.index');
    }

    public function update(PurchaseUpdateRequest $request, Purchase $purchase): RedirectResponse
    {
        abort_if($purchase->deleted, 404);

        DB::transaction(function () use ($request, $purchase) {
            $previousSupplierId = $purchase->supplier_id;
            $previousDueAmount = (float) $purchase->due_amount;

            $this->removeDetailsFromStock($purchase);
            $purchase->details()->delete();
            $this->savePurchase($purchase, $request->validated(), $request->user()?->id);
            $this->createDetails($purchase, $request->validated('items'), $request->user()?->id);

            $this->adjustSupplierBalance($previousSupplierId, $previousDueAmount);
            $this->adjustSupplierBalance($purchase->supplier_id, -(float) $purchase->due_amount);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Purchase updated.')]);

        return to_route('purchases.index');
    }

    public function destroy(Request $request, Purchase $purchase): RedirectResponse
    {
        abort_if($purchase->deleted, 404);

        DB::transaction(function () use ($request, $purchase) {
            $this->adjustSupplierBalance($purchase->supplier_id, (float) $purchase->due_amount);
            $this->removeDetailsFromStock($purchase);

            $purchase->details()->update([
                'deleted' => 1,
                'deleted_at' => now(),
                'deleted_by' => $request->user()?->id,
            ]);

            $purchase->update([
                'deleted' => 1,
                'deleted_at' => now(),
                'deleted_by' => $request->user()?->id,
            ]);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Purchase deleted.')]);

        return to_route('purchases.index');
    }

    public function payment(Request $request, Purchase $purchase): RedirectResponse
    {
        abort_if($purchase->deleted, 404);

        DB::transaction(function () use ($request, $purchase) {
            $purchase = Purchase::query()
                ->whereKey($purchase->id)
                ->lockForUpdate()
                ->firstOrFail();

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
            $dueAmount = (float) $purchase->due_amount;

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

            $paidAmount = (float) $purchase->paid_amount + $paymentAmount;
            $remainingDueAmount = max(0, (float) $purchase->subtotal - $paidAmount);

            $purchase->update([
                'paid_amount' => $paidAmount,
                'due_amount' => $remainingDueAmount,
                'payment_status' => $remainingDueAmount <= 0 ? 1 : 2,
                'payment_date' => now()->toDateString(),
                'updated_by' => $request->user()?->id,
            ]);

            $account->update([
                'available_balance' => (float) $account->available_balance - $paymentAmount,
                'updated_by' => $request->user()?->id,
            ]);

            Transaction::query()->create([
                'transaction_no' => $this->nextTransactionNumber(),
                'date' => now()->toDateString(),
                'account_id' => $account->id,
                'payment_type' => 1,
                'transaction_type' => 0,
                'reference_type' => Purchase::class,
                'reference_description' => $purchase->purchase_number,
                'description' => 'Purchase payment for '.$purchase->purchase_number,
                'total_amount' => $paymentAmount,
                'notes' => $validated['note'] ?? null,
                'created_by' => $request->user()?->id,
                'updated_by' => $request->user()?->id,
            ]);

            $this->adjustSupplierBalance($purchase->supplier_id, $paymentAmount);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Payment recorded.')]);

        return to_route('purchases.index');
    }

    public function receive(Request $request, Purchase $purchase): RedirectResponse
    {
        abort_if($purchase->deleted, 404);

        DB::transaction(function () use ($request, $purchase) {
            $purchase = Purchase::query()
                ->whereKey($purchase->id)
                ->lockForUpdate()
                ->firstOrFail();

            $purchase->details()
                ->where('deleted', 0)
                ->where('receive_status', '!=', 1)
                ->get()
                ->each(function (PurchaseDetail $detail) use ($request) {
                    $this->receiveDetailIntoStock($detail, $request->user()?->id);
                });

            $purchase->update([
                'receive_status' => 1,
                'updated_by' => $request->user()?->id,
            ]);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Purchase received.')]);

        return to_route('purchases.index');
    }

    private function savePurchase(Purchase $purchase, array $validated, ?int $userId): Purchase
    {
        $items = collect($validated['items']);
        $subtotal = $items->sum(
            fn (array $item) => (float) $item['purchase_price'] * (int) $item['purchase_qty'],
        );
        $paidAmount = $purchase->exists
            ? min((float) $purchase->paid_amount, $subtotal)
            : 0;
        $dueAmount = max(0, $subtotal - $paidAmount);
        $paymentStatus = $paidAmount <= 0 ? 0 : ($dueAmount <= 0 ? 1 : 2);

        $purchase->fill([
            'purchase_number' => $purchase->exists ? $purchase->purchase_number : $this->nextPurchaseNumber(),
            'supplier_id' => $validated['supplier_id'],
            'total_product' => $items->count(),
            'subtotal' => $subtotal,
            'paid_amount' => $paidAmount,
            'due_amount' => $dueAmount,
            'payment_status' => $paymentStatus,
            'purchase_date' => $validated['purchase_date'],
            'note' => $validated['note'] ?? null,
            'receive_status' => 0,
            'updated_by' => $userId,
        ]);

        if (! $purchase->exists) {
            $purchase->created_by = $userId;
        }

        $purchase->save();

        return $purchase;
    }

    private function createDetails(Purchase $purchase, array $items, ?int $userId): void
    {
        foreach (array_values($items) as $index => $item) {
            $quantity = (int) $item['purchase_qty'];
            $purchasePrice = (float) $item['purchase_price'];

            $purchase->details()->create([
                'purchase_detail_number' => $this->nextPurchaseDetailNumber($purchase, $index + 1),
                'product_id' => $item['product_id'],
                'purchase_price' => $purchasePrice,
                'sell_price' => $item['sell_price'],
                'purchase_qty' => $quantity,
                'sell_qty' => 0,
                'available_qty' => 0,
                'total_amount' => $purchasePrice * $quantity,
                'expire_date' => $item['expire_date'] ?? null,
                'receive_status' => 0,
                'status' => 1,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
        }
    }

    private function receiveDetailIntoStock(PurchaseDetail $detail, ?int $userId): void
    {
        $availableQty = max(0, $detail->purchase_qty - $detail->sell_qty);

        $detail->update([
            'available_qty' => $availableQty,
            'receive_status' => 1,
            'updated_by' => $userId,
        ]);

        if ($availableQty <= 0) {
            return;
        }

        Product::query()
            ->whereKey($detail->product_id)
            ->increment('stock_quantity', $availableQty);
    }

    private function removeDetailsFromStock(Purchase $purchase): void
    {
        $purchase->details()
            ->where('deleted', 0)
            ->where('receive_status', 1)
            ->get(['product_id', 'available_qty'])
            ->each(function (PurchaseDetail $detail) {
                $product = Product::query()->find($detail->product_id);

                if ($product) {
                    $product->update([
                        'stock_quantity' => max(0, $product->stock_quantity - $detail->available_qty),
                    ]);
                }
            });
    }

    private function adjustSupplierBalance(int $supplierId, float $amount): void
    {
        if ($amount === 0.0) {
            return;
        }

        $supplier = Supplier::query()
            ->whereKey($supplierId)
            ->lockForUpdate()
            ->firstOrFail();

        $supplier->update([
            'balance_amount' => (float) $supplier->balance_amount + $amount,
        ]);
    }

    private function moneyToCents(float $amount): int
    {
        return (int) round($amount * 100);
    }

    private function nextPurchaseNumber(): string
    {
        $lastNumber = Purchase::query()
            ->where('purchase_number', 'like', 'PUR-%')
            ->pluck('purchase_number')
            ->map(function (string $purchaseNumber): ?int {
                return preg_match('/^PUR-(\d+)$/', $purchaseNumber, $matches)
                    ? (int) $matches[1]
                    : null;
            })
            ->filter()
            ->max();

        return 'PUR-'.(max(1000, $lastNumber ?? 0) + 1);
    }

    private function nextPurchaseDetailNumber(Purchase $purchase, int $line): string
    {
        return $purchase->purchase_number.'-'.str_pad((string) $line, 3, '0', STR_PAD_LEFT);
    }

    private function nextTransactionNumber(): string
    {
        $lastNumber = Transaction::query()
            ->where('transaction_no', 'like', 'TRX-%')
            ->pluck('transaction_no')
            ->map(function (string $transactionNumber): ?int {
                return preg_match('/^TRX-(\d+)$/', $transactionNumber, $matches)
                    ? (int) $matches[1]
                    : null;
            })
            ->filter()
            ->max();

        return 'TRX-'.(max(1000, $lastNumber ?? 0) + 1);
    }

    private function paymentAccountOptions(): array
    {
        return BankAccount::query()
            ->where('deleted', 0)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get(['id', 'name', 'account_number', 'available_balance', 'is_default'])
            ->map(fn (BankAccount $bankAccount) => [
                'id' => $bankAccount->id,
                'name' => $bankAccount->name,
                'account_number' => $bankAccount->account_number,
                'available_balance' => $bankAccount->available_balance,
                'is_default' => $bankAccount->is_default,
            ])
            ->all();
    }

    private function formOptions(): array
    {
        return [
            'suppliers' => Supplier::query()
                ->where('deleted', 0)
                ->where('status', 1)
                ->orderBy('name')
                ->get(['id', 'name', 'photo', 'email', 'phone', 'address', 'note'])
                ->map(fn (Supplier $supplier) => [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'photo_url' => $supplier->photo
                        ? Storage::disk('public')->url($supplier->photo)
                        : null,
                    'email' => $supplier->email,
                    'phone' => $supplier->phone,
                    'address' => $supplier->address,
                    'note' => $supplier->note,
                ]),
            'products' => Product::query()
                ->with('measurementUnit:id,short_name')
                ->where('deleted', 0)
                ->where('status', 1)
                ->orderBy('name')
                ->get(['id', 'name', 'sku', 'thumbnail', 'unit_id', 'regular_price', 'sale_price', 'stock_quantity'])
                ->map(fn (Product $product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'thumbnail_url' => $product->thumbnail
                        ? Storage::disk('public')->url($product->thumbnail)
                        : null,
                    'unit' => $product->measurementUnit?->short_name,
                    'regular_price' => $product->regular_price,
                    'sale_price' => $product->sale_price,
                    'stock_quantity' => $product->stock_quantity,
                ]),
        ];
    }

    private function purchaseFormData(Purchase $purchase): array
    {
        return [
            'id' => $purchase->id,
            'purchase_number' => $purchase->purchase_number,
            'supplier_id' => $purchase->supplier_id,
            'purchase_date' => $purchase->purchase_date?->format('Y-m-d'),
            'note' => $purchase->note,
            'items' => $purchase->details->map(fn (PurchaseDetail $detail) => [
                'product_id' => $detail->product_id,
                'purchase_qty' => $detail->purchase_qty,
                'expire_date' => $detail->expire_date?->format('Y-m-d'),
                'purchase_price' => $detail->purchase_price,
                'sell_price' => $detail->sell_price,
            ]),
        ];
    }
}
