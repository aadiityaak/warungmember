<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Inertia\Response;

class VoucherController extends Controller
{
    public function index(): Response
    {
        return inertia('admin/vouchers/Index', [
            'vouchers' => Voucher::latest()->get(),
        ]);
    }

    public function create(): Response
    {
        return inertia('admin/vouchers/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:vouchers,code|max:50',
            'type' => 'required|in:birthday,golden_hour,manual',
            'discount_type' => 'required|in:percent,fixed',
            'discount_value' => 'required|integer|min:1',
            'min_purchase' => 'integer|min:0',
            'max_discount' => 'nullable|integer|min:0',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'is_active' => 'boolean',
        ]);

        Voucher::create($validated);

        return redirect()->route('admin.vouchers.index')
            ->with('toast', ['type' => 'success', 'message' => 'Voucher berhasil dibuat.']);
    }

    public function destroy(Voucher $voucher)
    {
        $voucher->delete();

        return redirect()->route('admin.vouchers.index')
            ->with('toast', ['type' => 'success', 'message' => 'Voucher berhasil dihapus.']);
    }
}
