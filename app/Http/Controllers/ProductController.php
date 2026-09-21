<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(): Response
    {
        $products = Product::query()
            ->with(['category:id,name', 'subcategory:id,name', 'measurementUnit:id,short_name'])
            ->where('deleted', 0)
            ->latest('id')
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'category_id' => $product->category_id,
                'category_name' => $product->category?->name,
                'subcategory_id' => $product->subcategory_id,
                'subcategory_name' => $product->subcategory?->name,
                'name' => $product->name,
                'slug' => $product->slug,
                'sku' => $product->sku,
                'thumbnail_url' => $product->thumbnail
                    ? Storage::disk('public')->url($product->thumbnail)
                    : null,
                'short_description' => $product->short_description,
                'description' => $product->description,
                'unit' => $product->measurementUnit?->short_name,
                'gross_weight' => $product->gross_weight,
                'weight' => $product->weight,
                'regular_price' => $product->regular_price,
                'sale_price' => $product->sale_price,
                'discount_percentage' => $product->discount_percentage,
                'badge' => $product->badge,
                'stock_quantity' => $product->stock_quantity,
                'minimum_order_quantity' => $product->minimum_order_quantity,
                'is_featured' => $product->is_featured,
                'status' => $product->status,
                'created_at' => $product->created_at?->format('Y-m-d'),
            ]);

        return Inertia::render('products/Index', [
            'products' => $products,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('products/Create', [
            ...$this->formOptions(),
            'nextSku' => $this->nextSku(),
        ]);
    }

    public function edit(Product $product): Response
    {
        abort_if($product->deleted, 404);

        return Inertia::render('products/Edit', [
            ...$this->formOptions(),
            'product' => $this->productFormData($product),
        ]);
    }

    public function store(ProductStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['slug'] = $this->uniqueSlug($validated['name']);
        $validated['thumbnail'] = $request->file('thumbnail')?->store('products', 'public');
        $validated['created_by'] = $request->user()?->id;
        $validated['updated_by'] = $request->user()?->id;

        Product::query()->create($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Product created.')]);

        return to_route('products.index');
    }

    public function update(ProductUpdateRequest $request, Product $product): RedirectResponse
    {
        abort_if($product->deleted, 404);

        $validated = $request->validated();
        $validated['slug'] = $this->uniqueSlug($validated['name'], $product);

        if ($request->hasFile('thumbnail')) {
            if ($product->thumbnail) {
                Storage::disk('public')->delete($product->thumbnail);
            }

            $validated['thumbnail'] = $request->file('thumbnail')->store('products', 'public');
        } else {
            unset($validated['thumbnail']);
        }

        $validated['updated_by'] = $request->user()?->id;

        $product->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Product updated.')]);

        return to_route('products.index');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        abort_if($product->deleted, 404);

        $product->update([
            'deleted' => 1,
            'deleted_at' => now(),
            'deleted_by' => $request->user()?->id,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Product deleted.')]);

        return to_route('products.index');
    }

    private function uniqueSlug(string $name, ?Product $product = null): string
    {
        $baseSlug = Str::slug($name) ?: 'product';
        $slug = $baseSlug;
        $suffix = 2;

        while (Product::query()
            ->where('slug', $slug)
            ->when($product, fn ($query) => $query->whereKeyNot($product->getKey()))
            ->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    private function formOptions(): array
    {
        return [
            'categories' => Category::query()
                ->where('deleted', 0)
                ->where('status', 1)
                ->orderBy('name')
                ->get(['id', 'name']),
            'subcategories' => Subcategory::query()
                ->where('deleted', 0)
                ->where('status', 1)
                ->orderBy('name')
                ->get(['id', 'category_id', 'name']),
            'units' => Unit::query()
                ->where('deleted', 0)
                ->where('status', 1)
                ->orderByDesc('default')
                ->orderBy('name')
                ->get(['id', 'name', 'short_name', 'default']),
        ];
    }

    private function nextSku(): string
    {
        $highestSku = Product::query()
            ->whereNotNull('sku')
            ->pluck('sku')
            ->filter(fn (string $sku) => ctype_digit($sku))
            ->reduce(
                fn (int $highest, string $sku) => max($highest, (int) $sku),
                1000,
            );

        return (string) ($highestSku + 1);
    }

    private function productFormData(Product $product): array
    {
        return [
            'id' => $product->id,
            'category_id' => $product->category_id,
            'subcategory_id' => $product->subcategory_id,
            'name' => $product->name,
            'slug' => $product->slug,
            'sku' => $product->sku,
            'thumbnail_url' => $product->thumbnail
                ? Storage::disk('public')->url($product->thumbnail)
                : null,
            'short_description' => $product->short_description,
            'description' => $product->description,
            'unit_id' => $product->unit_id,
            'gross_weight' => $product->gross_weight,
            'weight' => $product->weight,
            'regular_price' => $product->regular_price,
            'sale_price' => $product->sale_price,
            'discount_percentage' => $product->discount_percentage,
            'badge' => $product->badge,
            'stock_quantity' => $product->stock_quantity,
            'minimum_order_quantity' => $product->minimum_order_quantity,
            'is_featured' => $product->is_featured,
            'status' => $product->status,
        ];
    }
}
