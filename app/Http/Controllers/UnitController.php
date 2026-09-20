<?php

namespace App\Http\Controllers;

use App\Http\Requests\UnitStoreRequest;
use App\Http\Requests\UnitUpdateRequest;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UnitController extends Controller
{
    public function index(): Response
    {
        $units = Unit::query()
            ->where('deleted', 0)
            ->latest('id')
            ->get()
            ->map(fn (Unit $unit) => [
                'id' => $unit->id,
                'name' => $unit->name,
                'default' => $unit->default,
                'status' => $unit->status,
                'created_at' => $unit->created_at?->format('Y-m-d'),
            ]);

        return Inertia::render('units/Index', ['units' => $units]);
    }

    public function store(UnitStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['created_by'] = $request->user()?->id;
        $validated['updated_by'] = $request->user()?->id;

        Unit::query()->create($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Unit created.')]);

        return to_route('units.index');
    }

    public function update(UnitUpdateRequest $request, Unit $unit): RedirectResponse
    {
        abort_if($unit->deleted, 404);

        $validated = $request->validated();
        $validated['updated_by'] = $request->user()?->id;

        $unit->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Unit updated.')]);

        return to_route('units.index');
    }

    public function toggleDefault(Request $request, Unit $unit): RedirectResponse
    {
        abort_if($unit->deleted, 404);

        $isDefault = ! $unit->default;

        if ($isDefault) {
            Unit::query()
                ->where('deleted', 0)
                ->whereKeyNot($unit->id)
                ->update(['default' => 0]);
        }

        $unit->update([
            'default' => (int) $isDefault,
            'updated_by' => $request->user()?->id,
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $isDefault ? __('Unit activated as default.') : __('Unit deactivated as default.'),
        ]);

        return to_route('units.index');
    }

    public function destroy(Request $request, Unit $unit): RedirectResponse
    {
        abort_if($unit->deleted, 404);

        $unit->update([
            'deleted' => 1,
            'deleted_at' => now(),
            'deleted_by' => $request->user()?->id,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Unit deleted.')]);

        return to_route('units.index');
    }
}
