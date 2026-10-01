<?php

namespace App\Http\Controllers;

use App\Http\Requests\FaqStoreRequest;
use App\Http\Requests\FaqUpdateRequest;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FaqController extends Controller
{
    public function index(): Response
    {
        $faqs = Faq::query()
            ->where('deleted', 0)
            ->orderBy('sort_order')
            ->latest('id')
            ->get()
            ->map(fn (Faq $faq) => [
                'id' => $faq->id,
                'title' => $faq->title,
                'items' => implode("\n", $faq->items ?? []),
                'item_count' => count($faq->items ?? []),
                'sort_order' => $faq->sort_order,
                'status' => $faq->status,
                'created_at' => $faq->created_at?->format('Y-m-d'),
            ]);

        return Inertia::render('backend/faqs/Index', [
            'faqs' => $faqs,
        ]);
    }

    public function show(): Response
    {
        $sections = Faq::query()
            ->where('deleted', 0)
            ->where('status', 1)
            ->orderBy('sort_order')
            ->latest('id')
            ->get()
            ->map(fn (Faq $faq) => [
                'title' => $faq->title,
                'items' => $faq->items ?? [],
            ])
            ->values()
            ->all();

        return Inertia::render('frontend/StaticPage', [
            'page' => [
                'title' => 'FAQ',
                'eyebrow' => 'Store Policies',
                'intro' => '',
                'sections' => $sections,
            ],
        ]);
    }

    public function store(FaqStoreRequest $request): RedirectResponse
    {
        $validated = $this->validatedPayload($request);
        $validated['created_by'] = $request->user()?->id;
        $validated['updated_by'] = $request->user()?->id;

        Faq::query()->create($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('FAQ item created.')]);

        return to_route('faqs.index');
    }

    public function update(FaqUpdateRequest $request, Faq $faq): RedirectResponse
    {
        abort_if($faq->deleted, 404);

        $validated = $this->validatedPayload($request);
        $validated['updated_by'] = $request->user()?->id;

        $faq->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('FAQ item updated.')]);

        return to_route('faqs.index');
    }

    public function destroy(Request $request, Faq $faq): RedirectResponse
    {
        abort_if($faq->deleted, 404);

        $faq->update([
            'deleted' => 1,
            'deleted_at' => now(),
            'deleted_by' => $request->user()?->id,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('FAQ item deleted.')]);

        return to_route('faqs.index');
    }

    private function validatedPayload(FaqStoreRequest|FaqUpdateRequest $request): array
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
