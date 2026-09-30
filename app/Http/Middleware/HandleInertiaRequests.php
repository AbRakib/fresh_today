<?php

namespace App\Http\Middleware;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Setting;
use App\Support\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $customer = $request->session()->has('customer_id')
            ? Customer::query()
                ->whereKey($request->session()->get('customer_id'))
                ->where('deleted', 0)
                ->where('status', 1)
                ->first(['id', 'name', 'email', 'phone', 'profile_image', 'address'])
            : null;
        $setting = Setting::query()
            ->where('deleted', 0)
            ->first(['company_name', 'email', 'phone', 'address', 'logo', 'meta_icon']);

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'settings' => [
                'company_name' => $setting?->company_name,
                'email' => $setting?->email,
                'phone' => $setting?->phone,
                'address' => $setting?->address,
                'logo_url' => $setting?->logo
                    ? Storage::disk('public')->url($setting->logo)
                    : null,
                'meta_icon_url' => $setting?->meta_icon
                    ? Storage::disk('public')->url($setting->meta_icon)
                    : null,
            ],
            'currency' => Currency::current(),
            'frontend_categories' => Category::query()
                ->where('deleted', 0)
                ->where('status', 1)
                ->orderBy('id')
                ->get(['id', 'name', 'icon'])
                ->map(fn (Category $category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'icon_url' => $category->icon
                        ? Storage::disk('public')->url($category->icon)
                        : null,
                ]),
            'frontend_search_products' => Product::query()
                ->with('category:id,name')
                ->where('deleted', 0)
                ->where('status', 1)
                ->latest('id')
                ->get(['id', 'category_id', 'name', 'slug', 'thumbnail', 'cost_price', 'sale_price'])
                ->map(fn (Product $product) => [
                    'id' => $product->id,
                    'category_name' => $product->category?->name,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'thumbnail_url' => $product->thumbnail
                        ? Storage::disk('public')->url($product->thumbnail)
                        : null,
                    'regular_price' => $product->cost_price,
                    'sale_price' => $product->sale_price,
                ]),
            'auth' => [
                'user' => $user ? [
                    ...$user->toArray(),
                    'avatar' => $user->photo
                        ? Storage::disk('public')->url($user->photo)
                        : null,
                ] : null,
                'customer' => $customer ? [
                    ...$customer->toArray(),
                    'avatar' => $customer->profile_image
                        ? Storage::disk('public')->url($customer->profile_image)
                        : null,
                ] : null,
            ],
            'customer_auth_modal' => fn () => $request->session()->get('customer_auth_modal'),
            'wishlist_count' => $customer?->wishlists()->count() ?? 0,
            'cart_count' => $customer?->carts()->sum('quantity') ?? 0,
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
