<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Response;

class VoucherController extends Controller
{
    public function index(): Response
    {
        $member = request()->user()->member;

        $myVouchers = $member?->memberVouchers()
            ->with('voucher')
            ->latest()
            ->paginate(20);

        $availableVouchers = Voucher::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', now());
            })
            ->latest()
            ->get();

        $claimedVoucherIds = $member?->memberVouchers()
            ->where('status', 'active')
            ->pluck('voucher_id')
            ->toArray() ?? [];

        return inertia('member/vouchers/Index', [
            'vouchers' => $myVouchers,
            'availableVouchers' => $availableVouchers,
            'memberPoints' => $member?->total_points ?? 0,
            'claimedVoucherIds' => $claimedVoucherIds,
        ]);
    }

    public function claim(Request $request, Voucher $voucher)
    {
        $member = $request->user()->member;

        if (! $member) {
            return back()->with('toast', ['type' => 'error', 'message' => 'Akun member tidak ditemukan.']);
        }

        if (! $voucher->is_active) {
            return back()->with('toast', ['type' => 'error', 'message' => 'Voucher tidak tersedia.']);
        }

        if ($voucher->valid_from && $voucher->valid_from->gt(now())) {
            return back()->with('toast', ['type' => 'error', 'message' => 'Voucher belum berlaku.']);
        }

        if ($voucher->valid_until && $voucher->valid_until->lt(now())) {
            return back()->with('toast', ['type' => 'error', 'message' => 'Voucher sudah kadaluarsa.']);
        }

        $alreadyClaimed = $member->memberVouchers()
            ->where('voucher_id', $voucher->id)
            ->where('status', 'active')
            ->exists();

        if ($alreadyClaimed) {
            return back()->with('toast', ['type' => 'error', 'message' => 'Kamu sudah memiliki voucher ini.']);
        }

        if ($voucher->points_required && $member->total_points < $voucher->points_required) {
            return back()->with('toast', ['type' => 'error', 'message' => 'Poin tidak mencukupi.']);
        }

        DB::transaction(function () use ($member, $voucher) {
            if ($voucher->points_required) {
                $member->decrement('total_points', $voucher->points_required);

                $member->pointTransactions()->create([
                    'type' => 'redeem',
                    'amount' => $voucher->points_required,
                    'reference_type' => Voucher::class,
                    'reference_id' => $voucher->id,
                    'note' => "Tukar voucher: {$voucher->code}",
                ]);
            }

            $member->memberVouchers()->create([
                'voucher_id' => $voucher->id,
                'status' => 'active',
                'redeemed_at' => now(),
            ]);
        });

        return back()->with('toast', ['type' => 'success', 'message' => "Voucher {$voucher->code} berhasil diklaim!"]);
    }
}
