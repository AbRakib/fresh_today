<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('authenticated users can view products', function () {
    $user = User::factory()->create();
    $category = Category::query()->create(['name' => 'Fish']);

    Product::query()->create([
        'category_id' => $category->id,
        'name' => 'Rui Fish',
        'slug' => 'rui-fish',
        'regular_price' => 350,
    ]);

    $this->actingAs($user)
        ->get(route('products.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('products/Index')
            ->has('products', 1)
            ->where('products.0.name', 'Rui Fish')
            ->where('products.0.category_name', 'Fish'));
});

test('authenticated users can open product create and edit pages', function () {
    $user = User::factory()->create();
    $category = Category::query()->create(['name' => 'Fish']);
    $subcategory = Subcategory::query()->create([
        'category_id' => $category->id,
        'name' => 'River Fish',
    ]);
    $product = Product::query()->create([
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'name' => 'Rui Fish',
        'slug' => 'rui-fish',
        'regular_price' => 350,
    ]);

    $this->actingAs($user)
        ->get(route('products.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('products/Create')
            ->has('categories', 1)
            ->has('subcategories', 1));

    $this->actingAs($user)
        ->get(route('products.edit', $product))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('products/Edit')
            ->where('product.id', $product->id)
            ->where('product.name', 'Rui Fish')
            ->has('categories', 1)
            ->has('subcategories', 1));
});

test('products can be created', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $category = Category::query()->create(['name' => 'Fish']);
    $subcategory = Subcategory::query()->create([
        'category_id' => $category->id,
        'name' => 'River Fish',
    ]);

    $this->actingAs($user)
        ->post(route('products.store'), [
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
            'name' => 'Rui Fish',
            'sku' => 'RUI-001',
            'thumbnail' => UploadedFile::fake()->image('rui.jpg'),
            'short_description' => 'Fresh river fish',
            'description' => 'Cleaned and packed fresh rui fish.',
            'unit' => 'kg',
            'weight' => '1 kg',
            'regular_price' => '350.00',
            'sale_price' => '325.00',
            'discount_percentage' => '7.14',
            'badge' => 'Fresh',
            'stock_quantity' => 20,
            'minimum_order_quantity' => 1,
            'is_featured' => 1,
            'status' => 1,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('products.index'));

    $product = Product::query()->where('sku', 'RUI-001')->firstOrFail();

    expect($product->slug)->toBe('rui-fish')
        ->and($product->created_by)->toBe($user->id)
        ->and($product->subcategory_id)->toBe($subcategory->id)
        ->and($product->is_featured)->toBe(1);
    Storage::disk('public')->assertExists($product->thumbnail);
});

test('products can be updated with a replacement thumbnail', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $category = Category::query()->create(['name' => 'Fish']);
    $oldThumbnail = UploadedFile::fake()->image('old.jpg')->store('products', 'public');
    $product = Product::query()->create([
        'category_id' => $category->id,
        'name' => 'Rui Fish',
        'slug' => 'rui-fish',
        'sku' => 'RUI-001',
        'thumbnail' => $oldThumbnail,
        'regular_price' => 350,
    ]);

    $this->actingAs($user)
        ->post(route('products.update', $product), [
            'category_id' => $category->id,
            'subcategory_id' => '',
            'name' => 'Premium Rui Fish',
            'sku' => 'RUI-002',
            'thumbnail' => UploadedFile::fake()->image('new.jpg'),
            'short_description' => '',
            'description' => '',
            'unit' => 'kg',
            'weight' => '1.5 kg',
            'regular_price' => '420.00',
            'sale_price' => '',
            'discount_percentage' => '',
            'badge' => 'Premium',
            'stock_quantity' => 12,
            'minimum_order_quantity' => 2,
            'is_featured' => 0,
            'status' => 1,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('products.index'));

    $product->refresh();

    expect($product->name)->toBe('Premium Rui Fish')
        ->and($product->slug)->toBe('premium-rui-fish')
        ->and($product->sku)->toBe('RUI-002')
        ->and($product->minimum_order_quantity)->toBe(2)
        ->and($product->updated_by)->toBe($user->id);
    Storage::disk('public')->assertMissing($oldThumbnail);
    Storage::disk('public')->assertExists($product->thumbnail);
});

test('products are soft deleted using product audit columns', function () {
    $user = User::factory()->create();
    $category = Category::query()->create(['name' => 'Fish']);
    $product = Product::query()->create([
        'category_id' => $category->id,
        'name' => 'Rui Fish',
        'slug' => 'rui-fish',
        'regular_price' => 350,
    ]);

    $this->actingAs($user)
        ->delete(route('products.destroy', $product))
        ->assertRedirect(route('products.index'));

    $product->refresh();

    expect($product->deleted)->toBe(1)
        ->and($product->deleted_at)->not->toBeNull()
        ->and($product->deleted_by)->toBe($user->id);
});
