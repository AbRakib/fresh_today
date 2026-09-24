<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeliveryChargeStoreRequest;
use App\Http\Requests\DeliveryChargeUpdateRequest;
use App\Models\DeliveryCharge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DeliveryChargeController extends Controller
{
    public function index(): Response
    {
        $deliveryCharges = DeliveryCharge::query()
            ->where('deleted', 0)
            ->latest('id')
            ->get()
            ->map(fn (DeliveryCharge $deliveryCharge) => [
                'id' => $deliveryCharge->id,
                'title' => $deliveryCharge->title,
                'amount' => $deliveryCharge->amount,
                'status' => $deliveryCharge->status,
                'created_at' => $deliveryCharge->created_at?->format('Y-m-d'),
            ]);

        return Inertia::render('backend/delivery-charges/Index', [
            'deliveryCharges' => $deliveryCharges,
        ]);
    }

    public function store(DeliveryChargeStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['status'] = 1;
        $validated['created_by'] = $request->user()?->id;
        $validated['updated_by'] = $request->user()?->id;

        DeliveryCharge::query()->create($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Delivery charge created.')]);

        return to_route('delivery-charges.index');
    }

    public function update(DeliveryChargeUpdateRequest $request, DeliveryCharge $deliveryCharge): RedirectResponse
    {
        abort_if($deliveryCharge->deleted, 404);

        $validated = $request->validated();
        $validated['updated_by'] = $request->user()?->id;

        $deliveryCharge->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Delivery charge updated.')]);

        return to_route('delivery-charges.index');
    }

    public function destroy(Request $request, DeliveryCharge $deliveryCharge): RedirectResponse
    {
        abort_if($deliveryCharge->deleted, 404);

        $deliveryCharge->update([
            'deleted' => 1,
            'deleted_at' => now(),
            'deleted_by' => $request->user()?->id,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Delivery charge deleted.')]);

        return to_route('delivery-charges.index');
    }
}
