<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShippingPolicyStoreRequest;
use App\Http\Requests\ShippingPolicyUpdateRequest;
use App\Models\ShippingPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShippingPolicyController extends Controller
{
    public function index(): Response
    {
        $shippingPolicies = ShippingPolicy::query()
            ->where('deleted', 0)
            ->orderBy('sort_order')
            ->latest('id')
            ->get()
            ->map(fn (ShippingPolicy $shippingPolicy) => [
                'id' => $shippingPolicy->id,
                'title' => $shippingPolicy->title,
                'items' => implode("\n", $shippingPolicy->items ?? []),
                'item_count' => count($shippingPolicy->items ?? []),
                'sort_order' => $shippingPolicy->sort_order,
                'status' => $shippingPolicy->status,
                'created_at' => $shippingPolicy->created_at?->format('Y-m-d'),
            ]);

        return Inertia::render('backend/shipping-policies/Index', [
            'shippingPolicies' => $shippingPolicies,
        ]);
    }

    public function show(): Response
    {
        $sections = ShippingPolicy::query()
            ->where('deleted', 0)
            ->where('status', 1)
            ->orderBy('sort_order')
            ->latest('id')
            ->get()
            ->map(fn (ShippingPolicy $shippingPolicy) => [
                'title' => $shippingPolicy->title,
                'items' => $shippingPolicy->items ?? [],
            ])
            ->values()
            ->all();

        return Inertia::render('frontend/StaticPage', [
            'page' => [
                'title' => 'Shipping Policy',
                'eyebrow' => 'Store Policies',
                'intro' => '',
                'sections' => $sections,
            ],
        ]);
    }

    public function store(ShippingPolicyStoreRequest $request): RedirectResponse
    {
        $validated = $this->validatedPayload($request);
        $validated['created_by'] = $request->user()?->id;
        $validated['updated_by'] = $request->user()?->id;

        ShippingPolicy::query()->create($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Shipping policy item created.')]);

        return to_route('shipping-policies.index');
    }

    public function update(ShippingPolicyUpdateRequest $request, ShippingPolicy $shippingPolicy): RedirectResponse
    {
        abort_if($shippingPolicy->deleted, 404);

        $validated = $this->validatedPayload($request);
        $validated['updated_by'] = $request->user()?->id;

        $shippingPolicy->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Shipping policy item updated.')]);

        return to_route('shipping-policies.index');
    }

    public function destroy(Request $request, ShippingPolicy $shippingPolicy): RedirectResponse
    {
        abort_if($shippingPolicy->deleted, 404);

        $shippingPolicy->update([
            'deleted' => 1,
            'deleted_at' => now(),
            'deleted_by' => $request->user()?->id,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Shipping policy item deleted.')]);

        return to_route('shipping-policies.index');
    }

    private function validatedPayload(ShippingPolicyStoreRequest|ShippingPolicyUpdateRequest $request): array
    {
        $validated = $request->validated();
        $validated['items'] = collect(preg_split('/\r\n|\r|\n/', $validated['items']))
            ->map(fn (string $item) => trim($item))
            ->filter()
            ->values()
            ->all();
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['status'] = $validated['status'] ?? 1;

        return $validated;
    }
}
