<?php

namespace App\Http\Controllers;

use App\Http\Requests\HelpCenterStoreRequest;
use App\Http\Requests\HelpCenterUpdateRequest;
use App\Models\HelpCenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HelpCenterController extends Controller
{
    public function index(): Response
    {
        $helpCenters = HelpCenter::query()
            ->where('deleted', 0)
            ->orderBy('sort_order')
            ->latest('id')
            ->get()
            ->map(fn (HelpCenter $helpCenter) => [
                'id' => $helpCenter->id,
                'title' => $helpCenter->title,
                'items' => implode("\n", $helpCenter->items ?? []),
                'item_count' => count($helpCenter->items ?? []),
                'sort_order' => $helpCenter->sort_order,
                'status' => $helpCenter->status,
                'created_at' => $helpCenter->created_at?->format('Y-m-d'),
            ]);

        return Inertia::render('backend/help-centers/Index', [
            'helpCenters' => $helpCenters,
        ]);
    }

    public function show(): Response
    {
        $sections = HelpCenter::query()
            ->where('deleted', 0)
            ->where('status', 1)
            ->orderBy('sort_order')
            ->latest('id')
            ->get()
            ->map(fn (HelpCenter $helpCenter) => [
                'title' => $helpCenter->title,
                'items' => $helpCenter->items ?? [],
            ])
            ->values()
            ->all();

        return Inertia::render('frontend/StaticPage', [
            'page' => [
                'title' => 'Help Center',
                'eyebrow' => 'Customer Support',
                'intro' => 'Find quick answers about ordering, payment, delivery, and getting support from Fresh Today.',
                'sections' => $sections ?: $this->defaultSections(),
            ],
        ]);
    }

    public function store(HelpCenterStoreRequest $request): RedirectResponse
    {
        $validated = $this->validatedPayload($request);
        $validated['created_by'] = $request->user()?->id;
        $validated['updated_by'] = $request->user()?->id;

        HelpCenter::query()->create($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Help center created.')]);

        return to_route('help-centers.index');
    }

    public function update(HelpCenterUpdateRequest $request, HelpCenter $helpCenter): RedirectResponse
    {
        abort_if($helpCenter->deleted, 404);

        $validated = $this->validatedPayload($request);
        $validated['updated_by'] = $request->user()?->id;

        $helpCenter->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Help center updated.')]);

        return to_route('help-centers.index');
    }

    public function destroy(Request $request, HelpCenter $helpCenter): RedirectResponse
    {
        abort_if($helpCenter->deleted, 404);

        $helpCenter->update([
            'deleted' => 1,
            'deleted_at' => now(),
            'deleted_by' => $request->user()?->id,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Help center deleted.')]);

        return to_route('help-centers.index');
    }

    private function validatedPayload(HelpCenterStoreRequest|HelpCenterUpdateRequest $request): array
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

    private function defaultSections(): array
    {
        return [
            [
                'title' => 'How can we help?',
                'items' => [
                    'Browse the shop, choose your fresh products, and add them to your cart.',
                    'Review your cart before checkout to confirm quantity, weight, price, and delivery details.',
                    'Contact our support team if you need help with an order, payment, delivery address, or product question.',
                ],
            ],
            [
                'title' => 'Order Support',
                'items' => [
                    'Keep your order information ready when contacting support so we can help faster.',
                    'If an item becomes unavailable, our team may contact you to confirm a suitable replacement or adjustment.',
                    'For urgent delivery questions, use the phone number shown in the site header or footer.',
                ],
            ],
        ];
    }
}
