<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerStoreRequest;
use App\Http\Requests\CustomerUpdateRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(): Response
    {
        $customers = Customer::query()
            ->where('deleted', 0)
            ->latest('id')
            ->get()
            ->map(fn (Customer $customer) => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'profile_image_url' => $customer->profile_image
                    ? Storage::disk('public')->url($customer->profile_image)
                    : null,
                'address' => $customer->address,
                'gender' => $customer->gender,
                'date_of_birth' => $customer->date_of_birth?->format('Y-m-d'),
                'status' => $customer->status,
            ]);

        return Inertia::render('customers/Index', ['customers' => $customers]);
    }

    public function store(CustomerStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['profile_image'] = $request->file('profile_image')?->store('customers', 'public');
        $validated['status'] = 1;
        $validated['created_by'] = $request->user()?->id;
        $validated['updated_by'] = $request->user()?->id;

        Customer::query()->create($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Customer created.')]);

        return to_route('customers.index');
    }

    public function update(CustomerUpdateRequest $request, Customer $customer): RedirectResponse
    {
        abort_if($customer->deleted, 404);

        $validated = $request->validated();

        if ($request->hasFile('profile_image')) {
            if ($customer->profile_image) {
                Storage::disk('public')->delete($customer->profile_image);
            }

            $validated['profile_image'] = $request->file('profile_image')->store('customers', 'public');
        } else {
            unset($validated['profile_image']);
        }

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        $validated['updated_by'] = $request->user()?->id;
        $customer->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Customer updated.')]);

        return to_route('customers.index');
    }

    public function destroy(Request $request, Customer $customer): RedirectResponse
    {
        abort_if($customer->deleted, 404);

        $customer->update([
            'deleted' => 1,
            'deleted_at' => now(),
            'deleted_by' => $request->user()?->id,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Customer deleted.')]);

        return to_route('customers.index');
    }
}
