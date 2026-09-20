<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierStoreRequest;
use App\Http\Requests\SupplierUpdateRequest;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SupplierController extends Controller
{
    public function index(): Response
    {
        $suppliers = Supplier::query()
            ->where('deleted', 0)
            ->latest('id')
            ->get()
            ->map(fn (Supplier $supplier) => [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'photo_url' => $supplier->photo
                    ? Storage::disk('public')->url($supplier->photo)
                    : null,
                'email' => $supplier->email,
                'phone' => $supplier->phone,
                'address' => $supplier->address,
                'note' => $supplier->note,
                'opening_balance_amount' => $supplier->opening_balance_amount,
                'opening_balance_date' => $supplier->opening_balance_date?->format('Y-m-d'),
                'status' => $supplier->status,
            ]);

        return Inertia::render('suppliers/Index', ['suppliers' => $suppliers]);
    }

    public function store(SupplierStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['photo'] = $request->file('photo')?->store('suppliers', 'public');
        $validated['status'] = 1;
        $validated['created_by'] = $request->user()?->id;
        $validated['updated_by'] = $request->user()?->id;

        Supplier::query()->create($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Supplier created.')]);

        return to_route('suppliers.index');
    }

    public function update(SupplierUpdateRequest $request, Supplier $supplier): RedirectResponse
    {
        abort_if($supplier->deleted, 404);

        $validated = $request->validated();

        if ($request->hasFile('photo')) {
            if ($supplier->photo) {
                Storage::disk('public')->delete($supplier->photo);
            }

            $validated['photo'] = $request->file('photo')->store('suppliers', 'public');
        } else {
            unset($validated['photo']);
        }

        $validated['updated_by'] = $request->user()?->id;
        $supplier->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Supplier updated.')]);

        return to_route('suppliers.index');
    }

    public function destroy(Request $request, Supplier $supplier): RedirectResponse
    {
        abort_if($supplier->deleted, 404);

        $supplier->update([
            'deleted' => 1,
            'deleted_at' => now(),
            'deleted_by' => $request->user()?->id,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Supplier deleted.')]);

        return to_route('suppliers.index');
    }
}
