<?php

namespace App\Http\Controllers;

use App\Http\Requests\SliderStoreRequest;
use App\Http\Requests\SliderUpdateRequest;
use App\Models\Product;
use App\Models\Slider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SliderController extends Controller
{
    public function index(): Response
    {
        $sliders = Slider::query()
            ->with('product:id,name,slug')
            ->where('deleted', 0)
            ->latest('id')
            ->get()
            ->map(fn (Slider $slider) => [
                'id' => $slider->id,
                'image_url' => Storage::disk('public')->url($slider->image),
                'product_id' => $slider->product_id,
                'product_name' => $slider->product?->name,
                'button' => $slider->button,
                'status' => $slider->status,
                'created_at' => $slider->created_at?->format('Y-m-d'),
            ]);

        return Inertia::render('backend/sliders/Index', [
            'sliders' => $sliders,
        ]);
    }

    public function frontendSliders()
    {
        return Slider::query()
            ->with('product:id,name,slug')
            ->where('deleted', 0)
            ->where('status', 1)
            ->latest('id')
            ->get()
            ->map(fn (Slider $slider) => [
                'id' => $slider->id,
                'image_url' => Storage::disk('public')->url($slider->image),
                'button' => $slider->button,
                'product' => $slider->product ? [
                    'name' => $slider->product->name,
                    'slug' => $slider->product->slug,
                ] : null,
            ]);
    }

    public function create(): Response
    {
        return Inertia::render('backend/sliders/Create', [
            'products' => $this->products(),
        ]);
    }

    public function edit(Slider $slider): Response
    {
        abort_if($slider->deleted, 404);

        return Inertia::render('backend/sliders/Edit', [
            'products' => $this->products(),
            'slider' => $this->sliderFormData($slider),
        ]);
    }

    public function store(SliderStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['image'] = $request->file('image')->store('sliders', 'public');
        $validated['created_by'] = $request->user()?->id;
        $validated['updated_by'] = $request->user()?->id;

        Slider::query()->create($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Slider has been created successfully.')]);

        return to_route('sliders.index');
    }

    public function update(SliderUpdateRequest $request, Slider $slider): RedirectResponse
    {
        abort_if($slider->deleted, 404);

        $validated = $request->validated();

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($slider->image);
            $validated['image'] = $request->file('image')->store('sliders', 'public');
        } else {
            unset($validated['image']);
        }

        $validated['updated_by'] = $request->user()?->id;

        $slider->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Slider has been updated successfully.')]);

        return to_route('sliders.index');
    }

    public function destroy(Request $request, Slider $slider): RedirectResponse
    {
        abort_if($slider->deleted, 404);

        $slider->update([
            'deleted' => 1,
            'deleted_at' => now(),
            'deleted_by' => $request->user()?->id,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Slider has been deleted successfully.')]);

        return to_route('sliders.index');
    }

    private function products()
    {
        return Product::query()
            ->where('deleted', 0)
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function sliderFormData(Slider $slider): array
    {
        return [
            'id' => $slider->id,
            'image_url' => Storage::disk('public')->url($slider->image),
            'product_id' => $slider->product_id,
            'button' => $slider->button,
            'status' => $slider->status,
        ];
    }
}
