<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Inertia\Response;

class VoucherController extends Controller
{
    public function __invoke(): Response
    {
        $member = request()->user()->member;

        $vouchers = $member?->memberVouchers()
            ->with('voucher')
            ->latest()
            ->paginate(20);

        return inertia('member/vouchers/Index', [
            'vouchers' => $vouchers,
        ]);
    }
}
