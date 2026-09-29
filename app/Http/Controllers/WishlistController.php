<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class WishlistController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        $customerId = $request->session()->get('customer_id');

        if (! $customerId) {
            return to_route('home')->with('customer_auth_modal', 'login');
        }

        $products = Wishlist::query()
            ->where('customer_id', $customerId)
            ->whereHas('product', fn ($query) => $query
                ->where('deleted', 0)
                ->where('status', 1))
            ->with([
                'product.category:id,name',
                'product.measurementUnit:id,short_name',
                'product.carts' => fn ($query) => $query->where('customer_id', $customerId),
            ])
            ->latest()
            ->get()
            ->map(function (Wishlist $wishlist) {
                $product = $wishlist->product;

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
                    'is_in_cart' => $product->carts->isNotEmpty(),
                ];
            });

        return Inertia::render('frontend/Wishlist', [
            'wishlist_products' => $products,
        ]);
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_if($product->deleted || ! $product->status, 404);

        $customerId = $request->session()->get('customer_id');

        if (! $customerId) {
            return back()->with('customer_auth_modal', 'login');
        }

        Wishlist::query()->firstOrCreate([
            'customer_id' => $customerId,
            'product_id' => $product->id,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Product added to wishlist.')]);

        return back();
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $customerId = $request->session()->get('customer_id');

        if (! $customerId) {
            return to_route('home')->with('customer_auth_modal', 'login');
        }

        Wishlist::query()
            ->where('customer_id', $customerId)
            ->where('product_id', $product->id)
            ->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Product removed from wishlist.')]);

        return back();
    }
}
