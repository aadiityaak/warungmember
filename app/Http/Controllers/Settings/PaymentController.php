<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    private function payment(): array
    {
        $defaults = [
            'qris' => [
                'enabled' => false,
                'merchant_name' => '',
                'merchant_id' => '',
                'qr_image' => null,
            ],
            'banks' => [
                // ['enabled' => true, 'bank_name' => '', 'account_number' => '', 'account_name' => ''],
            ],
        ];

        $data = Storage::exists('payment.json')
            ? json_decode(Storage::get('payment.json'), true)
            : [];

        return array_merge($defaults, $data);
    }

    public function edit(): Response
    {
        return Inertia::render('settings/Payment', [
            'payment' => $this->payment(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'qris.enabled' => 'boolean',
            'qris.merchant_name' => 'required_with:qris.enabled|string|max:255',
            'qris.merchant_id' => 'required_with:qris.enabled|string|max:255',
            'qris.qr_image' => 'nullable|string|max:500',
            'qris.qr_file' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'banks' => 'nullable|array',
            'banks.*.enabled' => 'boolean',
            'banks.*.bank_name' => 'required|string|max:100',
            'banks.*.account_number' => 'required|string|max:50',
            'banks.*.account_name' => 'required|string|max:255',
        ]);

        // Handle QR image upload
        if ($request->hasFile('qris.qr_file')) {
            $path = $request->file('qris.qr_file')->store('payments', 'public');
            $validated['qris']['qr_image'] = Storage::url($path);

            // Delete old image
            $old = $this->payment()['qris']['qr_image'] ?? null;
            if ($old) {
                $oldPath = str_replace('/storage/', '', $old);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
        } else {
            // Preserve existing qr_image if not re-uploaded
            $validated['qris']['qr_image'] = $validated['qris']['qr_image']
                ?? $this->payment()['qris']['qr_image'];
        }

        unset($validated['qris']['qr_file']);

        Storage::put('payment.json', json_encode($validated, JSON_PRETTY_PRINT));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Pembayaran berhasil diperbarui.']);

        return to_route('payment.edit');
    }
}
