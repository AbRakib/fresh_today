<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        $customerId = $request->session()->get('customer_id');

        if (! $customerId) {
            return to_route('home')->with('customer_auth_modal', 'login');
        }

        $cartItems = Cart::query()
            ->where('customer_id', $customerId)
            ->whereHas('product', fn ($query) => $query
                ->where('deleted', 0)
                ->where('status', 1))
            ->with(['product.category:id,name', 'product.measurementUnit:id,short_name'])
            ->latest()
            ->get()
            ->map(function (Cart $cart) {
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
                    'discount_percentage' => $product->discount_percentage,
                    'badge' => $product->badge,
                    'stock_quantity' => $product->stock_quantity,
                    'quantity' => $cart->quantity,
                    'line_total' => number_format((float) $price * $cart->quantity, 2, '.', ''),
                ];
            });

        return Inertia::render('frontend/Cart', [
            'cart_products' => $cartItems,
        ]);
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_if($product->deleted || ! $product->status, 404);

        $customerId = $request->session()->get('customer_id');

        if (! $customerId) {
            return back()->with('customer_auth_modal', 'login');
        }

        if ($product->stock_quantity < 1) {
            Inertia::flash('toast', ['type' => 'error', 'message' => __('This product is out of stock.')]);

            return back();
        }

        Cart::query()->updateOrCreate(
            [
                'customer_id' => $customerId,
                'product_id' => $product->id,
            ],
            [
                'quantity' => max(1, (int) $product->minimum_order_quantity),
            ],
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Product added to cart.')]);

        return back();
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $customerId = $request->session()->get('customer_id');

        if (! $customerId) {
            return to_route('home')->with('customer_auth_modal', 'login');
        }

        Cart::query()
            ->where('customer_id', $customerId)
            ->where('product_id', $product->id)
            ->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Product removed from cart.')]);

        return back();
    }
}
