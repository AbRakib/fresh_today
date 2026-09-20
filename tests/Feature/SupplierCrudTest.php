<?php

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('authenticated users can view suppliers', function () {
    $user = User::factory()->create();
    Supplier::query()->create(['name' => 'Fresh Foods Ltd', 'email' => 'sales@fresh.test']);

    $this->actingAs($user)
        ->get(route('suppliers.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('suppliers/Index')
            ->has('suppliers', 1)
            ->where('suppliers.0.name', 'Fresh Foods Ltd'));
});

test('suppliers can be created', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('suppliers.store'), [
            'name' => 'Fresh Foods Ltd',
            'photo' => UploadedFile::fake()->image('supplier.jpg'),
            'email' => 'sales@fresh.test',
            'phone' => '01700000000',
            'address' => 'Dhaka',
            'note' => 'Weekly delivery',
            'opening_balance_amount' => '12500.50',
            'opening_balance_date' => '2026-09-01',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('suppliers.index'));

    $supplier = Supplier::query()->where('email', 'sales@fresh.test')->firstOrFail();

    expect($supplier->created_by)->toBe($user->id)
        ->and($supplier->note)->toBe('Weekly delivery')
        ->and($supplier->opening_balance_amount)->toBe('12500.50')
        ->and($supplier->opening_balance_date?->format('Y-m-d'))->toBe('2026-09-01');
    Storage::disk('public')->assertExists($supplier->photo);
});

test('suppliers can be updated with a replacement photo', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $oldPhoto = UploadedFile::fake()->image('old.jpg')->store('suppliers', 'public');
    $supplier = Supplier::query()->create([
        'name' => 'Fresh Foods Ltd',
        'email' => 'sales@fresh.test',
        'photo' => $oldPhoto,
    ]);

    $this->actingAs($user)
        ->post(route('suppliers.update', $supplier), [
            'name' => 'Fresh Foods BD Ltd',
            'photo' => UploadedFile::fake()->image('new.jpg'),
            'email' => 'sales@fresh.test',
            'phone' => '01800000000',
            'address' => 'Chattogram',
            'note' => 'Updated note',
            'opening_balance_amount' => '9500.25',
            'opening_balance_date' => '2026-09-15',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('suppliers.index'));

    $supplier->refresh();

    expect($supplier->name)->toBe('Fresh Foods BD Ltd')
        ->and($supplier->opening_balance_amount)->toBe('9500.25')
        ->and($supplier->opening_balance_date?->format('Y-m-d'))->toBe('2026-09-15')
        ->and($supplier->updated_by)->toBe($user->id);
    Storage::disk('public')->assertMissing($oldPhoto);
    Storage::disk('public')->assertExists($supplier->photo);
});

test('suppliers are soft deleted using the supplier audit columns', function () {
    $user = User::factory()->create();
    $supplier = Supplier::query()->create(['name' => 'Fresh Foods Ltd']);

    $this->actingAs($user)
        ->delete(route('suppliers.destroy', $supplier))
        ->assertRedirect(route('suppliers.index'));

    $supplier->refresh();

    expect($supplier->deleted)->toBe(1)
        ->and($supplier->deleted_at)->not->toBeNull()
        ->and($supplier->deleted_by)->toBe($user->id);
});
