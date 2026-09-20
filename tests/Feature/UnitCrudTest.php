<?php

use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('authenticated users can view units', function () {
    $user = User::factory()->create();
    Unit::query()->create(['name' => 'Kilogram']);

    $this->actingAs($user)
        ->get(route('units.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('units/Index')
            ->has('units', 1)
            ->where('units.0.name', 'Kilogram'));
});

test('units can be created', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('units.store'), [
            'name' => 'Kilogram',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('units.index'));

    $unit = Unit::query()->where('name', 'Kilogram')->firstOrFail();

    expect($unit->default)->toBe(0)
        ->and($unit->status)->toBe(1)
        ->and($unit->created_by)->toBe($user->id);
});

test('units can be renamed without changing default or status', function () {
    $user = User::factory()->create();
    $unit = Unit::query()->create([
        'name' => 'Piece',
        'default' => 1,
        'status' => 0,
    ]);

    $this->actingAs($user)
        ->post(route('units.update', $unit), [
            'name' => 'Pieces',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('units.index'));

    $unit->refresh();

    expect($unit->name)->toBe('Pieces')
        ->and($unit->default)->toBe(1)
        ->and($unit->status)->toBe(0)
        ->and($unit->updated_by)->toBe($user->id);
});

test('a unit can be activated and deactivated as default', function () {
    $user = User::factory()->create();
    $previousDefault = Unit::query()->create(['name' => 'Kilogram', 'default' => 1]);
    $unit = Unit::query()->create(['name' => 'Piece']);

    $this->actingAs($user)
        ->post(route('units.toggle-default', $unit))
        ->assertRedirect(route('units.index'));

    expect($unit->refresh()->default)->toBe(1)
        ->and($unit->updated_by)->toBe($user->id)
        ->and($previousDefault->refresh()->default)->toBe(0);

    $this->actingAs($user)
        ->post(route('units.toggle-default', $unit))
        ->assertRedirect(route('units.index'));

    expect($unit->refresh()->default)->toBe(0);
});

test('units are soft deleted using the unit audit columns', function () {
    $user = User::factory()->create();
    $unit = Unit::query()->create(['name' => 'Kilogram']);

    $this->actingAs($user)
        ->delete(route('units.destroy', $unit))
        ->assertRedirect(route('units.index'));

    $unit->refresh();

    expect($unit->deleted)->toBe(1)
        ->and($unit->deleted_at)->not->toBeNull()
        ->and($unit->deleted_by)->toBe($user->id);
});
