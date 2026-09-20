<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubcategoryStoreRequest;
use App\Http\Requests\SubcategoryUpdateRequest;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SubcategoryController extends Controller
{
    public function index(): Response
    {
        $subcategories = Subcategory::query()
            ->with('category:id,name')
            ->where('deleted', 0)
            ->latest('id')
            ->get()
            ->map(fn (Subcategory $subcategory) => [
                'id' => $subcategory->id,
                'category_id' => $subcategory->category_id,
                'category_name' => $subcategory->category?->name,
                'name' => $subcategory->name,
                'icon_url' => $subcategory->icon
                    ? Storage::disk('public')->url($subcategory->icon)
                    : null,
                'status' => $subcategory->status,
                'created_at' => $subcategory->created_at?->format('Y-m-d'),
            ]);

        $categories = Category::query()
            ->where('deleted', 0)
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('subcategories/Index', [
            'subcategories' => $subcategories,
            'categories' => $categories,
        ]);
    }

    public function store(SubcategoryStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['icon'] = $request->file('icon')?->store('subcategories', 'public');
        $validated['created_by'] = $request->user()?->id;
        $validated['updated_by'] = $request->user()?->id;

        Subcategory::query()->create($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Subcategory created.')]);

        return to_route('subcategories.index');
    }

    public function update(SubcategoryUpdateRequest $request, Subcategory $subcategory): RedirectResponse
    {
        abort_if($subcategory->deleted, 404);

        $validated = $request->validated();

        if ($request->hasFile('icon')) {
            if ($subcategory->icon) {
                Storage::disk('public')->delete($subcategory->icon);
            }

            $validated['icon'] = $request->file('icon')->store('subcategories', 'public');
        } else {
            unset($validated['icon']);
        }

        $validated['updated_by'] = $request->user()?->id;

        $subcategory->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Subcategory updated.')]);

        return to_route('subcategories.index');
    }

    public function destroy(Request $request, Subcategory $subcategory): RedirectResponse
    {
        abort_if($subcategory->deleted, 404);

        $subcategory->update([
            'deleted' => 1,
            'deleted_at' => now(),
            'deleted_by' => $request->user()?->id,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Subcategory deleted.')]);

        return to_route('subcategories.index');
    }
}
