<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class BrandingController extends Controller
{
    private function branding(): array
    {
        $defaults = [
            'app_name' => config('app.name'),
            'logo_url' => null,
            'favicon_url' => null,
            'primary_color' => '#E22625',
            'whatsapp_number' => '081335405231',
        ];

        $data = Storage::exists('branding.json')
          ? json_decode(Storage::get('branding.json'), true)
          : [];

        // Filter out null/empty values so defaults win
        $data = array_filter($data ?? [], fn ($v) => $v !== null && $v !== '');

        return array_merge($defaults, $data);
    }

    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Branding', [
            'branding' => $this->branding(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'logo_url' => 'nullable|string|max:500',
            'favicon_url' => 'nullable|string|max:500',
            'primary_color' => 'required|string|max:7',
            'whatsapp_number' => 'nullable|string|max:20',
            'logo_file' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'favicon_file' => 'nullable|image|mimes:png,jpg,jpeg,ico,svg|max:512',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo_file')) {
            $path = $request->file('logo_file')->store('branding', 'public');
            $validated['logo_url'] = Storage::url($path);

            // Delete old logo if exists
            if ($old = ($this->branding()['logo_url'] ?? null)) {
                $oldPath = str_replace('/storage/', '', $old);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
        }

        // Handle favicon upload
        if ($request->hasFile('favicon_file')) {
            $path = $request->file('favicon_file')->store('branding', 'public');
            $validated['favicon_url'] = Storage::url($path);

            // Delete old favicon if exists
            if ($old = ($this->branding()['favicon_url'] ?? null)) {
                $oldPath = str_replace('/storage/', '', $old);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
        }

        unset($validated['logo_file'], $validated['favicon_file']);

        Storage::put('branding.json', json_encode($validated, JSON_PRETTY_PRINT));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Branding berhasil diperbarui.']);

        return to_route('branding.edit');
    }
}
