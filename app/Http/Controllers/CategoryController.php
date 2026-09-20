<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        $categories = Category::query()
            ->where('deleted', 0)
            ->latest('id')
            ->get()
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'icon_url' => $category->icon
                    ? Storage::disk('public')->url($category->icon)
                    : null,
                'status' => $category->status,
                'created_at' => $category->created_at?->format('Y-m-d'),
            ]);

        return Inertia::render('categories/Index', ['categories' => $categories]);
    }

    public function store(CategoryStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['icon'] = $request->file('icon')?->store('categories', 'public');
        $validated['created_by'] = $request->user()?->id;
        $validated['updated_by'] = $request->user()?->id;

        Category::query()->create($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Category created.')]);

        return to_route('categories.index');
    }

    public function update(CategoryUpdateRequest $request, Category $category): RedirectResponse
    {
        abort_if($category->deleted, 404);

        $validated = $request->validated();

        if ($request->hasFile('icon')) {
            if ($category->icon) {
                Storage::disk('public')->delete($category->icon);
            }

            $validated['icon'] = $request->file('icon')->store('categories', 'public');
        } else {
            unset($validated['icon']);
        }

        $validated['updated_by'] = $request->user()?->id;

        $category->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Category updated.')]);

        return to_route('categories.index');
    }

    public function destroy(Request $request, Category $category): RedirectResponse
    {
        abort_if($category->deleted, 404);

        $category->update([
            'deleted' => 1,
            'deleted_at' => now(),
            'deleted_by' => $request->user()?->id,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Category deleted.')]);

        return to_route('categories.index');
    }
}
