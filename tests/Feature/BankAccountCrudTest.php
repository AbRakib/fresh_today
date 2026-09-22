<?php

use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('authenticated users can view bank accounts', function () {
    $user = User::factory()->create();
    BankAccount::query()->create([
        'name' => 'Cash On Hand',
        'slug' => 'cash-on-hand',
    ]);

    $this->actingAs($user)
        ->get(route('bank-accounts.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('bank-accounts/Index')
            ->has('bankAccounts', 1)
            ->where('bankAccounts.0.name', 'Cash On Hand'));
});

test('bank accounts can be created with an opening balance', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('bank-accounts.store'), [
            'name' => 'City Bank',
            'account_number' => '123456789',
            'opening_balance' => '1250.50',
            'opening_balance_date' => '2026-09-22',
            'is_default' => '1',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('bank-accounts.index'));

    $account = BankAccount::query()->where('slug', 'city-bank')->firstOrFail();

    expect($account->available_balance)->toBe('1250.50')
        ->and($account->opening_balance)->toBe('1250.50')
        ->and($account->can_edit)->toBe(1)
        ->and($account->is_default)->toBe(1)
        ->and($account->created_by)->toBe($user->id);
});

test('updating an opening balance adjusts the available balance by the difference', function () {
    $user = User::factory()->create();
    $account = BankAccount::query()->create([
        'name' => 'City Bank',
        'slug' => 'city-bank',
        'available_balance' => 1500,
        'opening_balance' => 1000,
        'can_edit' => 1,
    ]);

    $this->actingAs($user)
        ->post(route('bank-accounts.update', $account), [
            'name' => 'City Bank Current',
            'account_number' => '987654321',
            'opening_balance' => '1200.00',
            'opening_balance_date' => '2026-09-22',
            'is_default' => '0',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('bank-accounts.index'));

    $account->refresh();

    expect($account->slug)->toBe('city-bank-current')
        ->and($account->available_balance)->toBe('1700.00')
        ->and($account->updated_by)->toBe($user->id);
});

test('only one bank account can be the default', function () {
    $user = User::factory()->create();
    $first = BankAccount::query()->create([
        'name' => 'Cash On Hand',
        'slug' => 'cash-on-hand',
        'is_default' => 1,
    ]);
    $second = BankAccount::query()->create([
        'name' => 'City Bank',
        'slug' => 'city-bank',
        'can_edit' => 1,
    ]);

    $this->actingAs($user)
        ->post(route('bank-accounts.toggle-default', $second))
        ->assertRedirect(route('bank-accounts.index'));

    expect($first->refresh()->is_default)->toBe(0)
        ->and($second->refresh()->is_default)->toBe(1);
});

test('editable bank accounts are soft deleted', function () {
    $user = User::factory()->create();
    $account = BankAccount::query()->create([
        'name' => 'City Bank',
        'slug' => 'city-bank',
        'can_edit' => 1,
    ]);

    $this->actingAs($user)
        ->delete(route('bank-accounts.destroy', $account))
        ->assertRedirect(route('bank-accounts.index'));

    expect($account->refresh()->deleted)->toBe(1)
        ->and($account->deleted_at)->not->toBeNull()
        ->and($account->deleted_by)->toBe($user->id);
});

test('protected bank accounts cannot be updated or deleted', function () {
    $user = User::factory()->create();
    $account = BankAccount::query()->create([
        'name' => 'Cash On Hand',
        'slug' => 'cash-on-hand',
        'can_edit' => 0,
    ]);

    $this->actingAs($user)
        ->post(route('bank-accounts.update', $account), [
            'name' => 'Changed',
            'opening_balance' => '0.00',
            'is_default' => '0',
        ])
        ->assertForbidden();

    $this->actingAs($user)
        ->delete(route('bank-accounts.destroy', $account))
        ->assertForbidden();
});
