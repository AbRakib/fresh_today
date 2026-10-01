<?php

namespace App\Http\Controllers;

use App\Http\Requests\TermConditionStoreRequest;
use App\Http\Requests\TermConditionUpdateRequest;
use App\Models\TermCondition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TermConditionController extends Controller
{
    public function index(): Response
    {
        $termConditions = TermCondition::query()
            ->where('deleted', 0)
            ->orderBy('sort_order')
            ->latest('id')
            ->get()
            ->map(fn (TermCondition $termCondition) => [
                'id' => $termCondition->id,
                'title' => $termCondition->title,
                'items' => implode("\n", $termCondition->items ?? []),
                'item_count' => count($termCondition->items ?? []),
                'sort_order' => $termCondition->sort_order,
                'status' => $termCondition->status,
                'created_at' => $termCondition->created_at?->format('Y-m-d'),
            ]);

        return Inertia::render('backend/term-conditions/Index', [
            'termConditions' => $termConditions,
        ]);
    }

    public function show(): Response
    {
        $sections = TermCondition::query()
            ->where('deleted', 0)
            ->where('status', 1)
            ->orderBy('sort_order')
            ->latest('id')
            ->get()
            ->map(fn (TermCondition $termCondition) => [
                'title' => $termCondition->title,
                'items' => $termCondition->items ?? [],
            ])
            ->values()
            ->all();

        return Inertia::render('frontend/StaticPage', [
            'page' => [
                'title' => 'Terms & Conditions',
                'eyebrow' => 'Store Policies',
                'intro' => '',
                'sections' => $sections,
            ],
        ]);
    }

    public function store(TermConditionStoreRequest $request): RedirectResponse
    {
        $validated = $this->validatedPayload($request);
        $validated['created_by'] = $request->user()?->id;
        $validated['updated_by'] = $request->user()?->id;

        TermCondition::query()->create($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Terms and conditions item created.')]);

        return to_route('term-conditions.index');
    }

    public function update(TermConditionUpdateRequest $request, TermCondition $termCondition): RedirectResponse
    {
        abort_if($termCondition->deleted, 404);

        $validated = $this->validatedPayload($request);
        $validated['updated_by'] = $request->user()?->id;

        $termCondition->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Terms and conditions item updated.')]);

        return to_route('term-conditions.index');
    }

    public function destroy(Request $request, TermCondition $termCondition): RedirectResponse
    {
        abort_if($termCondition->deleted, 404);

        $termCondition->update([
            'deleted' => 1,
            'deleted_at' => now(),
            'deleted_by' => $request->user()?->id,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Terms and conditions item deleted.')]);

        return to_route('term-conditions.index');
    }

    private function validatedPayload(TermConditionStoreRequest|TermConditionUpdateRequest $request): array
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
