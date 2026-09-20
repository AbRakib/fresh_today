<?php

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('authenticated users can view customers', function () {
    $user = User::factory()->create();
    Customer::query()->create([
        'name' => 'Nadia Rahman',
        'email' => 'nadia@example.com',
        'password' => 'password',
    ]);

    $this->actingAs($user)
        ->get(route('customers.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('customers/Index')
            ->has('customers', 1)
            ->where('customers.0.name', 'Nadia Rahman'));
});

test('customers can be created', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('customers.store'), [
            'name' => 'Nadia Rahman',
            'email' => 'nadia@example.com',
            'phone' => '01700000000',
            'profile_image' => UploadedFile::fake()->image('nadia.jpg'),
            'address' => 'Dhaka',
            'password' => 'password',
            'password_confirmation' => 'password',
            'gender' => 'female',
            'date_of_birth' => '1995-05-10',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('customers.index'));

    $customer = Customer::query()->where('email', 'nadia@example.com')->firstOrFail();

    expect($customer->created_by)->toBe($user->id)
        ->and($customer->password)->not->toBe('password');
    Storage::disk('public')->assertExists($customer->profile_image);
});

test('customers can be updated with a new password', function () {
    $user = User::factory()->create();
    $customer = Customer::query()->create([
        'name' => 'Nadia Rahman',
        'email' => 'nadia@example.com',
        'password' => 'password',
    ]);
    $this->actingAs($user)
        ->post(route('customers.update', $customer), [
            'name' => 'Nadia Ahmed',
            'email' => 'nadia@example.com',
            'phone' => '01800000000',
            'address' => '',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
            'gender' => '',
            'date_of_birth' => '',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('customers.index'));

    $customer->refresh();

    expect($customer->name)->toBe('Nadia Ahmed')
        ->and($customer->phone)->toBe('01800000000')
        ->and($customer->status)->toBe(1)
        ->and($customer->password)->not->toBe('new-password')
        ->and($customer->updated_by)->toBe($user->id);
});

test('customers are soft deleted using the customer audit columns', function () {
    $user = User::factory()->create();
    $customer = Customer::query()->create([
        'name' => 'Nadia Rahman',
        'email' => 'nadia@example.com',
        'password' => 'password',
    ]);

    $this->actingAs($user)
        ->delete(route('customers.destroy', $customer))
        ->assertRedirect(route('customers.index'));

    $customer->refresh();

    expect($customer->deleted)->toBe(1)
        ->and($customer->deleted_at)->not->toBeNull()
        ->and($customer->deleted_by)->toBe($user->id);
});
