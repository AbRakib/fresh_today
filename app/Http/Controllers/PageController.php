<?php

namespace App\Http\Controllers;

use App\Http\Requests\PageStoreRequest;
use App\Http\Requests\PageUpdateRequest;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function index(): Response
    {
        $pages = Page::query()
            ->where('deleted', 0)
            ->where('slug', '!=', 'terms-and-conditions')
            ->where('slug', '!=', 'shipping-policy')
            ->where('slug', '!=', 'return-policy')
            ->where('slug', '!=', 'faq')
            ->latest('id')
            ->get()
            ->map(fn (Page $page) => $this->backendPayload($page));

        return Inertia::render('backend/pages/Index', [
            'pages' => $pages,
        ]);
    }

    public function show(string $slug): Response
    {
        $page = Page::query()
            ->where('slug', $slug)
            ->where('deleted', 0)
            ->where('status', 1)
            ->firstOrFail();

        return Inertia::render('frontend/StaticPage', [
            'page' => [
                'title' => $page->title,
                'eyebrow' => $page->eyebrow ?: $page->title,
                'intro' => $page->intro ?: '',
                'sections' => $page->sections ?? [],
            ],
        ]);
    }

    public function store(PageStoreRequest $request): RedirectResponse
    {
        $validated = $this->validatedPayload($request);
        $validated['created_by'] = $request->user()?->id;
        $validated['updated_by'] = $request->user()?->id;

        Page::query()->create($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Page created.')]);

        return to_route('pages.index');
    }

    public function update(PageUpdateRequest $request, Page $page): RedirectResponse
    {
        abort_if($page->deleted, 404);

        $validated = $this->validatedPayload($request);
        $validated['updated_by'] = $request->user()?->id;

        $page->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Page updated.')]);

        return to_route('pages.index');
    }

    public function destroy(Request $request, Page $page): RedirectResponse
    {
        abort_if($page->deleted, 404);

        $page->update([
            'deleted' => 1,
            'deleted_at' => now(),
            'deleted_by' => $request->user()?->id,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Page deleted.')]);

        return to_route('pages.index');
    }

    private function validatedPayload(PageStoreRequest|PageUpdateRequest $request): array
    {
        $validated = $request->validated();
        $validated['slug'] = str($validated['slug'])->slug()->toString();
        $validated['sections'] = $this->parseSections($validated['sections']);
        $validated['status'] = $validated['status'] ?? 1;

        return $validated;
    }

    private function parseSections(string $sections): array
    {
        return collect(preg_split('/\R{2,}/', trim($sections)))
            ->map(function (string $block) {
                $lines = collect(preg_split('/\r\n|\r|\n/', $block))
                    ->map(fn (string $line) => trim($line))
                    ->filter()
                    ->values();

                if ($lines->isEmpty()) {
                    return null;
                }

                return [
                    'title' => $lines->shift(),
                    'items' => $lines->values()->all(),
                ];
            })
            ->filter(fn (?array $section) => $section && count($section['items']) > 0)
            ->values()
            ->all();
    }

    private function backendPayload(Page $page): array
    {
        $sections = $page->sections ?? [];

        return [
            'id' => $page->id,
            'title' => $page->title,
            'slug' => $page->slug,
            'eyebrow' => $page->eyebrow,
            'intro' => $page->intro,
            'sections' => $this->sectionsToText($sections),
            'section_count' => count($sections),
            'status' => $page->status,
            'created_at' => $page->created_at?->format('Y-m-d'),
        ];
    }

    private function sectionsToText(array $sections): string
    {
        return collect($sections)
            ->map(function (array $section) {
                return collect([$section['title'] ?? null, ...($section['items'] ?? [])])
                    ->filter()
                    ->implode("\n");
            })
            ->filter()
            ->implode("\n\n");
    }
}
