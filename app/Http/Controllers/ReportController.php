<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ReportController extends Controller
{
    public function stock(Request $request): Response
    {
        return Inertia::render('backend/reports/Stock', $this->stockData($request));
    }

    public function stockPdf(Request $request): HttpResponse
    {
        $data = $this->stockData($request);

        return Pdf::loadView('reports.stock-pdf', $data)
            ->setPaper('a4', 'landscape')
            ->download('stock-report.pdf');
    }

    private function stockData(Request $request): array
    {
        $search = trim((string) $request->query('search', ''));

        $products = Product::query()
            ->with(['category:id,name', 'subcategory:id,name', 'measurementUnit:id,short_name'])
            ->where('deleted', 0)
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            }))
            ->withSum(['purchaseDetails as purchased_qty' => fn ($query) => $query->where('deleted', 0)->where('receive_status', 1)], 'purchase_qty')
            ->withSum(['purchaseDetails as sold_qty' => fn ($query) => $query->where('deleted', 0)->where('receive_status', 1)], 'sell_qty')
            ->withSum(['purchaseDetails as available_qty' => fn ($query) => $query->where('deleted', 0)->where('receive_status', 1)], 'available_qty')
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'thumbnail_url' => $product->thumbnail
                    ? Storage::disk('public')->url($product->thumbnail)
                    : null,
                'category_name' => $product->category?->name,
                'subcategory_name' => $product->subcategory?->name,
                'unit' => $product->measurementUnit?->short_name,
                'purchased_qty' => (int) ($product->purchased_qty ?? 0),
                'sold_qty' => (int) ($product->sold_qty ?? 0),
                'available_qty' => (int) ($product->available_qty ?? 0),
                'stock_quantity' => (int) $product->stock_quantity,
                'stock_value' => (float) DB::table('purchase_details')
                    ->where('product_id', $product->id)
                    ->where('deleted', 0)
                    ->where('receive_status', 1)
                    ->sum(DB::raw('available_qty * purchase_price')),
            ]);

        return [
            'filters' => ['search' => $search],
            'products' => $products,
            'summary' => [
                'total_products' => $products->count(),
                'total_purchased_qty' => $products->sum('purchased_qty'),
                'total_sold_qty' => $products->sum('sold_qty'),
                'total_stock_qty' => $products->sum('stock_quantity'),
                'total_stock_value' => $products->sum('stock_value'),
            ],
        ];
    }

    public function profitLoss(Request $request): Response
    {
        return Inertia::render('backend/reports/ProfitLoss', $this->profitLossData($request));
    }

    public function profitLossPdf(Request $request): HttpResponse
    {
        $data = $this->profitLossData($request);

        return Pdf::loadView('reports.profit-loss-pdf', $data)
            ->setPaper('a4', 'landscape')
            ->download('profit-loss-report.pdf');
    }

    private function profitLossData(Request $request): array
    {
        $period = (string) $request->query('period', 'today');
        $today = Carbon::today();

        [$startDate, $endDate] = match ($period) {
            'yesterday' => [$today->copy()->subDay()->toDateString(), $today->copy()->subDay()->toDateString()],
            'this_month' => [$today->copy()->startOfMonth()->toDateString(), $today->copy()->endOfMonth()->toDateString()],
            'last_month' => [$today->copy()->subMonthNoOverflow()->startOfMonth()->toDateString(), $today->copy()->subMonthNoOverflow()->endOfMonth()->toDateString()],
            'this_year' => [$today->copy()->startOfYear()->toDateString(), $today->copy()->endOfYear()->toDateString()],
            'date_range' => [$request->query('start_date'), $request->query('end_date')],
            default => [$today->toDateString(), $today->toDateString()],
        };

        $orders = Order::query()
            ->where('deleted', 0)
            ->when($startDate, fn (Builder $query) => $query->whereDate('order_date', '>=', $startDate))
            ->when($endDate, fn (Builder $query) => $query->whereDate('order_date', '<=', $endDate))
            ->with(['customer:id,name', 'details' => fn ($query) => $query
                ->where('deleted', 0)
                ->with('purchaseDetail:id,purchase_price')])
            ->latest('order_date')
            ->latest('id')
            ->get()
            ->map(function (Order $order) {
                $costAmount = $order->details->sum(fn ($detail) => (float) ($detail->purchaseDetail?->purchase_price ?? 0) * (int) $detail->order_qty);
                $productSales = (float) $order->subtotal - (float) $order->discount_amount;
                $profitAmount = (float) $order->total_amount - $costAmount;

                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer?->name,
                    'order_date' => $order->order_date?->format('Y-m-d'),
                    'subtotal' => (float) $order->subtotal,
                    'discount_amount' => (float) $order->discount_amount,
                    'delivery_charge' => (float) $order->delivery_charge,
                    'total_amount' => (float) $order->total_amount,
                    'cost_amount' => $costAmount,
                    'product_profit' => $productSales - $costAmount,
                    'profit_amount' => $profitAmount,
                ];
            });

        return [
            'filters' => [
                'period' => $period,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'orders' => $orders,
            'summary' => [
                'order_count' => $orders->count(),
                'subtotal' => $orders->sum('subtotal'),
                'discount_amount' => $orders->sum('discount_amount'),
                'delivery_charge' => $orders->sum('delivery_charge'),
                'total_amount' => $orders->sum('total_amount'),
                'cost_amount' => $orders->sum('cost_amount'),
                'product_profit' => $orders->sum('product_profit'),
                'profit_amount' => $orders->sum('profit_amount'),
            ],
        ];
    }
}
