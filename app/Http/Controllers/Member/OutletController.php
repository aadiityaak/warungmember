<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Inertia\Response;

class OutletController extends Controller
{
    public function index(): Response
    {
        return inertia('member/outlets/Index', [
            'outlets' => Outlet::with('kasir')
                ->where('is_active', true)
                ->get(),
            'lastOutletId' => auth()->user()->member?->last_outlet_id,
        ]);
    }

    public function show(Outlet $outlet): Response
    {
        return inertia('member/outlets/Show', [
            'outlet' => $outlet->load('kasir'),
        ]);
    }

    public function select(Request $request)
    {
        $validated = $request->validate([
            'outlet_id' => 'required|exists:outlets,id',
        ]);

        $member = auth()->user()->member;
        if ($member) {
            $member->update(['last_outlet_id' => $validated['outlet_id']]);
        }

        return redirect()->route('member.outlets.index')
            ->with('toast', ['type' => 'success', 'message' => 'Outlet berhasil dipilih.']);
    }
}
