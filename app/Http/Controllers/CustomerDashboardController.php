<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CustomerDashboardController extends Controller
{
    public function __invoke(Request $request): Response|RedirectResponse
    {
        $customer = $request->session()->has('customer_id')
            ? $request->session()->get('customer_id')
            : null;

        if (! $customer) {
            return to_route('home')->with('customer_auth_modal', 'login');
        }

        $orders = Order::query()
            ->with(['details' => fn ($query) => $query->where('deleted', 0)->with('product:id,name,sku,thumbnail')])
            ->where('customer_id', $customer)
            ->where('deleted', 0)
            ->latest('id')
            ->get();

        return Inertia::render('frontend/CustomerDashboard', [
            'orders' => $orders->map(fn (Order $order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total_product' => $order->total_product,
                'subtotal' => $order->subtotal,
                'delivery_charge' => $order->delivery_charge,
                'total_amount' => $order->total_amount,
                'paid_amount' => $order->paid_amount,
                'due_amount' => $order->due_amount,
                'payment_status' => $order->payment_status,
                'order_status' => $order->order_status,
                'order_date' => $order->order_date?->format('Y-m-d'),
                'delivery_date' => $order->delivery_date?->format('Y-m-d'),
                'delivery_address' => $order->delivery_address,
                'note' => $order->note,
                'details' => $order->details->map(fn (OrderDetail $detail) => [
                    'id' => $detail->id,
                    'product_name' => $detail->product?->name ?? 'Unknown product',
                    'product_sku' => $detail->product?->sku,
                    'thumbnail_url' => $detail->product?->thumbnail
                        ? Storage::disk('public')->url($detail->product->thumbnail)
                        : null,
                    'order_qty' => $detail->order_qty,
                    'sale_price' => $detail->sale_price,
                    'total_amount' => $detail->total_amount,
                ]),
            ]),
        ]);
    }
}
