<?php

use App\Models\DeliveryCharge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('authenticated users can view delivery charges', function () {
    $user = User::factory()->create();
    DeliveryCharge::query()->create([
        'title' => 'Inside Dhaka',
        'amount' => 80,
    ]);

    $this->actingAs($user)
        ->get(route('delivery-charges.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('delivery-charges/Index')
            ->has('deliveryCharges', 1)
            ->where('deliveryCharges.0.title', 'Inside Dhaka'));
});

test('delivery charges can be created', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('delivery-charges.store'), [
            'title' => 'Inside Dhaka',
            'amount' => '80.00',
            'status' => '1',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('delivery-charges.index'));

    $deliveryCharge = DeliveryCharge::query()->where('title', 'Inside Dhaka')->firstOrFail();

    expect($deliveryCharge->amount)->toBe('80.00')
        ->and($deliveryCharge->status)->toBe(1)
        ->and($deliveryCharge->created_by)->toBe($user->id);
});

test('delivery charge titles must be unique regardless of letter case', function (string $title) {
    $user = User::factory()->create();
    DeliveryCharge::query()->create([
        'title' => 'Title',
        'amount' => 80,
    ]);

    $this->actingAs($user)
        ->post(route('delivery-charges.store'), [
            'title' => $title,
            'amount' => '100.00',
        ])
        ->assertSessionHasErrors('title');

    expect(DeliveryCharge::query()->count())->toBe(1);
})->with(['title', 'TITLE', 'TiTlE']);

test('delivery charges can be updated', function () {
    $user = User::factory()->create();
    $deliveryCharge = DeliveryCharge::query()->create([
        'title' => 'Inside Dhaka',
        'amount' => 80,
        'status' => 0,
    ]);

    $this->actingAs($user)
        ->post(route('delivery-charges.update', $deliveryCharge), [
            'title' => 'Outside Dhaka',
            'amount' => '120.00',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('delivery-charges.index'));

    $deliveryCharge->refresh();

    expect($deliveryCharge->title)->toBe('Outside Dhaka')
        ->and($deliveryCharge->amount)->toBe('120.00')
        ->and($deliveryCharge->status)->toBe(0)
        ->and($deliveryCharge->updated_by)->toBe($user->id);
});

test('delivery charge updates reject another title regardless of letter case', function () {
    $user = User::factory()->create();
    DeliveryCharge::query()->create([
        'title' => 'Inside Dhaka',
        'amount' => 80,
    ]);
    $deliveryCharge = DeliveryCharge::query()->create([
        'title' => 'Outside Dhaka',
        'amount' => 120,
    ]);

    $this->actingAs($user)
        ->post(route('delivery-charges.update', $deliveryCharge), [
            'title' => 'INSIDE DHAKA',
            'amount' => '120.00',
        ])
        ->assertSessionHasErrors('title');

    expect($deliveryCharge->fresh()->title)->toBe('Outside Dhaka');
});

test('delivery charges are soft deleted using audit columns', function () {
    $user = User::factory()->create();
    $deliveryCharge = DeliveryCharge::query()->create([
        'title' => 'Inside Dhaka',
        'amount' => 80,
    ]);

    $this->actingAs($user)
        ->delete(route('delivery-charges.destroy', $deliveryCharge))
        ->assertRedirect(route('delivery-charges.index'));

    $deliveryCharge->refresh();

    expect($deliveryCharge->deleted)->toBe(1)
        ->and($deliveryCharge->deleted_at)->not->toBeNull()
        ->and($deliveryCharge->deleted_by)->toBe($user->id);
});
