<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReturnPolicyStoreRequest;
use App\Http\Requests\ReturnPolicyUpdateRequest;
use App\Models\ReturnPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReturnPolicyController extends Controller
{
    public function index(): Response
    {
        $returnPolicies = ReturnPolicy::query()
            ->where('deleted', 0)
            ->orderBy('sort_order')
            ->latest('id')
            ->get()
            ->map(fn (ReturnPolicy $returnPolicy) => [
                'id' => $returnPolicy->id,
                'title' => $returnPolicy->title,
                'items' => implode("\n", $returnPolicy->items ?? []),
                'item_count' => count($returnPolicy->items ?? []),
                'sort_order' => $returnPolicy->sort_order,
                'status' => $returnPolicy->status,
                'created_at' => $returnPolicy->created_at?->format('Y-m-d'),
            ]);

        return Inertia::render('backend/return-policies/Index', [
            'returnPolicies' => $returnPolicies,
        ]);
    }

    public function show(): Response
    {
        $sections = ReturnPolicy::query()
            ->where('deleted', 0)
            ->where('status', 1)
            ->orderBy('sort_order')
            ->latest('id')
            ->get()
            ->map(fn (ReturnPolicy $returnPolicy) => [
                'title' => $returnPolicy->title,
                'items' => $returnPolicy->items ?? [],
            ])
            ->values()
            ->all();

        return Inertia::render('frontend/StaticPage', [
            'page' => [
                'title' => 'Return Policy',
                'eyebrow' => 'Store Policies',
                'intro' => '',
                'sections' => $sections,
            ],
        ]);
    }

    public function store(ReturnPolicyStoreRequest $request): RedirectResponse
    {
        $validated = $this->validatedPayload($request);
        $validated['created_by'] = $request->user()?->id;
        $validated['updated_by'] = $request->user()?->id;

        ReturnPolicy::query()->create($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Return policy item created.')]);

        return to_route('return-policies.index');
    }

    public function update(ReturnPolicyUpdateRequest $request, ReturnPolicy $returnPolicy): RedirectResponse
    {
        abort_if($returnPolicy->deleted, 404);

        $validated = $this->validatedPayload($request);
        $validated['updated_by'] = $request->user()?->id;

        $returnPolicy->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Return policy item updated.')]);

        return to_route('return-policies.index');
    }

    public function destroy(Request $request, ReturnPolicy $returnPolicy): RedirectResponse
    {
        abort_if($returnPolicy->deleted, 404);

        $returnPolicy->update([
            'deleted' => 1,
            'deleted_at' => now(),
            'deleted_by' => $request->user()?->id,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Return policy item deleted.')]);

        return to_route('return-policies.index');
    }

    private function validatedPayload(ReturnPolicyStoreRequest|ReturnPolicyUpdateRequest $request): array
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
