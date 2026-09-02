<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\CompanySettingUpdateRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CompanySettingController extends Controller
{
    public function edit(): Response
    {
        $setting = Setting::query()->where('deleted', 0)->first();

        return Inertia::render('settings/Company', [
            'setting' => [
                'company_name' => $setting?->company_name ?? '',
                'email' => $setting?->email ?? '',
                'phone' => $setting?->phone ?? '',
                'address' => $setting?->address ?? '',
                'logo' => $setting?->logo,
                'meta_icon' => $setting?->meta_icon,
                'logo_url' => $setting?->logo ? Storage::disk('public')->url($setting->logo) : null,
                'meta_icon_url' => $setting?->meta_icon ? Storage::disk('public')->url($setting->meta_icon) : null,
            ],
        ]);
    }

    public function update(CompanySettingUpdateRequest $request): RedirectResponse
    {
        $setting = Setting::query()->where('deleted', 0)->firstOrNew();
        $validated = $request->validated();

        foreach (['logo', 'meta_icon'] as $fileField) {
            if (! $request->hasFile($fileField)) {
                unset($validated[$fileField]);

                continue;
            }

            if ($setting->{$fileField}) {
                Storage::disk('public')->delete($setting->{$fileField});
            }

            $validated[$fileField] = $request->file($fileField)->store('settings', 'public');
        }

        $setting->fill($validated);

        if (! $setting->exists) {
            $setting->created_by = $request->user()?->id;
        }

        $setting->updated_by = $request->user()?->id;
        $setting->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Company settings updated.')]);

        return to_route('company.edit');
    }
}
