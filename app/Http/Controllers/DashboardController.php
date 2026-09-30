<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $today = Carbon::today();
        $monthStart = $today->copy()->startOfMonth();
        $trendStart = $today->copy()->subDays(6);

        $orders = Order::query()->where('deleted', 0);
        $purchases = Purchase::query()->where('deleted', 0);
        $products = Product::query()->where('deleted', 0);

        $salesTrend = collect(range(0, 6))->map(function (int $offset) use ($trendStart) {
            $date = $trendStart->copy()->addDays($offset);
            $dayOrders = Order::query()
                ->where('deleted', 0)
                ->whereDate('order_date', $date->toDateString());

            return [
                'label' => $date->format('M d'),
                'date' => $date->toDateString(),
                'sales' => (float) (clone $dayOrders)->sum('total_amount'),
                'orders' => (int) (clone $dayOrders)->count(),
            ];
        });

        $orderStatuses = collect([
            ['label' => 'Pending', 'value' => 0, 'class' => 'bg-amber-500'],
            ['label' => 'Processing', 'value' => 1, 'class' => 'bg-sky-500'],
            ['label' => 'Delivered', 'value' => 2, 'class' => 'bg-emerald-500'],
            ['label' => 'Cancelled', 'value' => 3, 'class' => 'bg-rose-500'],
        ])->map(fn (array $status) => [
            ...$status,
            'count' => (int) Order::query()
                ->where('deleted', 0)
                ->where('order_status', $status['value'])
                ->count(),
        ]);

        return Inertia::render('backend/Dashboard', [
            'summary' => [
                'today_sales' => (float) (clone $orders)->whereDate('order_date', $today->toDateString())->sum('total_amount'),
                'today_orders' => (int) (clone $orders)->whereDate('order_date', $today->toDateString())->count(),
                'month_sales' => (float) (clone $orders)->whereDate('order_date', '>=', $monthStart->toDateString())->sum('total_amount'),
                'month_purchases' => (float) (clone $purchases)->whereDate('purchase_date', '>=', $monthStart->toDateString())->sum('subtotal'),
                'order_due' => (float) (clone $orders)->sum('due_amount'),
                'purchase_due' => (float) (clone $purchases)->sum('due_amount'),
                'products' => (int) (clone $products)->count(),
                'active_products' => (int) (clone $products)->where('status', 1)->count(),
                'customers' => (int) Customer::query()->where('deleted', 0)->count(),
                'suppliers' => (int) Supplier::query()->where('deleted', 0)->count(),
                'stock_value' => (float) DB::table('purchase_details')
                    ->where('deleted', 0)
                    ->where('receive_status', 1)
                    ->sum(DB::raw('available_qty * purchase_price')),
                'cash_balance' => (float) BankAccount::query()->where('deleted', 0)->sum('available_balance'),
            ],
            'salesTrend' => $salesTrend,
            'orderStatuses' => $orderStatuses,
            'recentOrders' => Order::query()
                ->with('customer:id,name,phone')
                ->where('deleted', 0)
                ->latest('id')
                ->limit(6)
                ->get()
                ->map(fn (Order $order) => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer?->name ?? 'Walk-in customer',
                    'customer_phone' => $order->customer?->phone,
                    'total_amount' => (float) $order->total_amount,
                    'due_amount' => (float) $order->due_amount,
                    'payment_status' => (int) $order->payment_status,
                    'order_status' => (int) $order->order_status,
                    'order_date' => $order->order_date?->format('Y-m-d'),
                ]),
            'lowStockProducts' => Product::query()
                ->with('measurementUnit:id,short_name')
                ->where('deleted', 0)
                ->orderBy('stock_quantity')
                ->limit(6)
                ->get(['id', 'name', 'sku', 'stock_quantity', 'minimum_order_quantity', 'unit_id'])
                ->map(fn (Product $product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'stock_quantity' => (int) $product->stock_quantity,
                    'minimum_order_quantity' => (int) $product->minimum_order_quantity,
                    'unit' => $product->measurementUnit?->short_name,
                ]),
            'topCategories' => Category::query()
                ->withCount(['products' => fn ($query) => $query->where('deleted', 0)])
                ->where('deleted', 0)
                ->orderByDesc('products_count')
                ->limit(5)
                ->get(['id', 'name'])
                ->map(fn (Category $category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'products_count' => (int) $category->products_count,
                ]),
            'recentTransactions' => Transaction::query()
                ->with('account:id,name')
                ->where('deleted', 0)
                ->latest('id')
                ->limit(5)
                ->get()
                ->map(fn (Transaction $transaction) => [
                    'id' => $transaction->id,
                    'transaction_no' => $transaction->transaction_no,
                    'date' => $transaction->date?->format('Y-m-d'),
                    'account_name' => $transaction->account?->name,
                    'description' => $transaction->description,
                    'transaction_type' => (int) $transaction->transaction_type,
                    'total_amount' => (float) $transaction->total_amount,
                ]),
        ]);
    }
}
