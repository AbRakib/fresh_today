<?php

use App\Models\Product;

it('calculates the customer price from sell price independently of cost', function ($sellPrice, $discount, $expected) {
    $product = new Product([
        'cost_price' => 100,
        'sale_price' => $sellPrice,
        'discount_percentage' => $discount,
    ]);

    expect($product->discounted_price)->toBe($expected);
    expect($product->cost_price)->toBe('100.00');
})->with([
    'no discount' => [500, null, '500.00'],
    'zero discount' => [500, 0, '500.00'],
    'five percent' => [500, 5, '475.00'],
    'fractional discount' => [199.99, 12.5, '174.99'],
    'full discount' => [500, 100, '0.00'],
]);
