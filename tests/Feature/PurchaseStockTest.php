<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('purchase creation does not update product stock until purchase is received', function () {
    $user = User::factory()->create();
    $category = Category::query()->create(['name' => 'Fish']);
    $supplier = Supplier::query()->create(['name' => 'Morning Supplier']);
    $product = Product::query()->create([
        'category_id' => $category->id,
        'name' => 'Rui Fish',
        'slug' => 'rui-fish',
        'regular_price' => 350,
        'stock_quantity' => 10,
    ]);

    $this->actingAs($user)
        ->post(route('purchases.store'), [
            'supplier_id' => $supplier->id,
            'purchase_date' => '2026-09-21',
            'items' => [
                [
                    'product_id' => $product->id,
                    'purchase_qty' => 7,
                    'purchase_price' => '300.00',
                    'sell_price' => '350.00',
                ],
            ],
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('purchases.index'));

    $product->refresh();
    $purchase = Purchase::query()->firstOrFail();
    $detail = PurchaseDetail::query()->firstOrFail();

    expect($product->stock_quantity)->toBe(10)
        ->and($purchase->receive_status)->toBe(0)
        ->and($detail->available_qty)->toBe(0)
        ->and($detail->receive_status)->toBe(0)
        ->and($supplier->refresh()->balance_amount)->toBe('-2100.00');

    $this->actingAs($user)
        ->post(route('purchases.receive', $purchase))
        ->assertRedirect(route('purchases.index'));

    $product->refresh();
    $purchase->refresh();
    $detail->refresh();

    expect($product->stock_quantity)->toBe(17)
        ->and($purchase->receive_status)->toBe(1)
        ->and($detail->available_qty)->toBe(7)
        ->and($detail->receive_status)->toBe(1);
});

test('purchase payment must equal the full due amount', function () {
    $user = User::factory()->create();
    $category = Category::query()->create(['name' => 'Vegetables']);
    $supplier = Supplier::query()->create([
        'name' => 'Green Supplier',
        'balance_amount' => 500,
    ]);
    $product = Product::query()->create([
        'category_id' => $category->id,
        'name' => 'Potato',
        'slug' => 'potato',
        'regular_price' => 50,
    ]);

    $this->actingAs($user)->post(route('purchases.store'), [
        'supplier_id' => $supplier->id,
        'purchase_date' => '2026-09-21',
        'items' => [[
            'product_id' => $product->id,
            'purchase_qty' => 10,
            'purchase_price' => '40.00',
            'sell_price' => '50.00',
        ]],
    ])->assertSessionHasNoErrors();

    $purchase = Purchase::query()->firstOrFail();

    expect($supplier->refresh()->balance_amount)->toBe('100.00');

    $this->actingAs($user)
        ->post(route('purchases.payment', $purchase), ['amount' => '150.00'])
        ->assertSessionHasErrors('amount');

    expect($supplier->refresh()->balance_amount)->toBe('100.00')
        ->and($purchase->refresh()->paid_amount)->toBe('0.00')
        ->and($purchase->due_amount)->toBe('400.00');

    $this->actingAs($user)
        ->post(route('purchases.payment', $purchase), ['amount' => '450.00'])
        ->assertSessionHasErrors('amount');

    expect($supplier->refresh()->balance_amount)->toBe('100.00')
        ->and($purchase->refresh()->paid_amount)->toBe('0.00')
        ->and($purchase->due_amount)->toBe('400.00');

    $this->actingAs($user)
        ->post(route('purchases.payment', $purchase), ['amount' => '400.00'])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('purchases.index'));

    expect($supplier->refresh()->balance_amount)->toBe('500.00')
        ->and($purchase->refresh()->paid_amount)->toBe('400.00')
        ->and($purchase->due_amount)->toBe('0.00')
        ->and($purchase->payment_status)->toBe(1);
});

test('receiving a purchase twice does not add stock twice', function () {
    $user = User::factory()->create();
    $category = Category::query()->create(['name' => 'Fish']);
    $supplier = Supplier::query()->create(['name' => 'Morning Supplier']);
    $product = Product::query()->create([
        'category_id' => $category->id,
        'name' => 'Katla Fish',
        'slug' => 'katla-fish',
        'regular_price' => 380,
        'stock_quantity' => 5,
    ]);
    $purchase = Purchase::query()->create([
        'purchase_number' => 'PUR-1001',
        'supplier_id' => $supplier->id,
        'total_product' => 1,
        'subtotal' => 1200,
        'due_amount' => 1200,
        'purchase_date' => '2026-09-21',
    ]);

    PurchaseDetail::query()->create([
        'purchase_detail_number' => 'PUR-1001-001',
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
        'purchase_price' => 300,
        'sell_price' => 380,
        'purchase_qty' => 4,
        'total_amount' => 1200,
    ]);

    $this->actingAs($user)->post(route('purchases.receive', $purchase));
    $this->actingAs($user)->post(route('purchases.receive', $purchase));

    expect($product->refresh()->stock_quantity)->toBe(9);
});
