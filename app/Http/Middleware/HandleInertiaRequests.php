<?php

namespace App\Http\Middleware;

use App\Models\Category;
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

        return [
            ...parent::share($request),
            'name' => config('app.name'),
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
            'auth' => [
                'user' => $user ? [
                    ...$user->toArray(),
                    'avatar' => $user->photo
                        ? Storage::disk('public')->url($user->photo)
                        : null,
                ] : null,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
