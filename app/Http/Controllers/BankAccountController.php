<?php

namespace App\Http\Controllers;

use App\Http\Requests\BankAccountStoreRequest;
use App\Http\Requests\BankAccountUpdateRequest;
use App\Models\BankAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BankAccountController extends Controller
{
    public function index(): Response
    {
        $bankAccounts = BankAccount::query()
            ->where('deleted', 0)
            ->orderByDesc('is_default')
            ->latest('id')
            ->get()
            ->map(fn (BankAccount $bankAccount) => [
                'id' => $bankAccount->id,
                'name' => $bankAccount->name,
                'slug' => $bankAccount->slug,
                'account_number' => $bankAccount->account_number,
                'available_balance' => $bankAccount->available_balance,
                'opening_balance' => $bankAccount->opening_balance,
                'opening_balance_date' => $bankAccount->opening_balance_date?->format('Y-m-d'),
                'can_edit' => $bankAccount->can_edit,
                'is_default' => $bankAccount->is_default,
                'created_at' => $bankAccount->created_at?->format('Y-m-d'),
            ]);

        return Inertia::render('backend/bank-accounts/Index', ['bankAccounts' => $bankAccounts]);
    }

    public function store(BankAccountStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($request, $validated): void {
            if ($validated['is_default']) {
                BankAccount::query()->where('deleted', 0)->update(['is_default' => 0]);
            }

            BankAccount::query()->create([
                ...$validated,
                'slug' => $this->uniqueSlug($validated['name']),
                'available_balance' => $validated['opening_balance'],
                'can_edit' => 1,
                'created_by' => $request->user()?->id,
                'updated_by' => $request->user()?->id,
            ]);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Bank account created.')]);

        return to_route('bank-accounts.index');
    }

    public function update(BankAccountUpdateRequest $request, BankAccount $bankAccount): RedirectResponse
    {
        abort_if($bankAccount->deleted, 404);
        abort_unless($bankAccount->can_edit, 403);

        $validated = $request->validated();

        DB::transaction(function () use ($request, $validated, $bankAccount): void {
            if ($validated['is_default']) {
                BankAccount::query()
                    ->where('deleted', 0)
                    ->whereKeyNot($bankAccount->id)
                    ->update(['is_default' => 0]);
            }

            $balanceAdjustment = (float) $validated['opening_balance'] - (float) $bankAccount->opening_balance;

            $bankAccount->update([
                ...$validated,
                'slug' => $this->uniqueSlug($validated['name'], $bankAccount->id),
                'available_balance' => (float) $bankAccount->available_balance + $balanceAdjustment,
                'updated_by' => $request->user()?->id,
            ]);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Bank account updated.')]);

        return to_route('bank-accounts.index');
    }

    public function toggleDefault(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        abort_if($bankAccount->deleted, 404);

        DB::transaction(function () use ($request, $bankAccount): void {
            BankAccount::query()
                ->where('deleted', 0)
                ->whereKeyNot($bankAccount->id)
                ->update(['is_default' => 0]);

            $bankAccount->update([
                'is_default' => 1,
                'updated_by' => $request->user()?->id,
            ]);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Default bank account updated.')]);

        return to_route('bank-accounts.index');
    }

    public function destroy(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        abort_if($bankAccount->deleted, 404);
        abort_unless($bankAccount->can_edit, 403);

        $bankAccount->update([
            'is_default' => 0,
            'deleted' => 1,
            'deleted_at' => now(),
            'deleted_by' => $request->user()?->id,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Bank account deleted.')]);

        return to_route('bank-accounts.index');
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name) ?: 'bank-account';
        $slug = $baseSlug;
        $suffix = 2;

        while (BankAccount::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $baseSlug.'-'.$suffix++;
        }

        return $slug;
    }
}
