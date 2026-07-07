<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $member = request()->user()->member;

        return inertia('member/Dashboard', [
            'stats' => [
                'total_points' => $member?->total_points ?? 0,
                'deposit_balance' => $member?->deposit_balance ?? 0,
                'active_vouchers' => $member?->memberVouchers()->where('status', 'active')->count() ?? 0,
            ],
        ]);
    }
}
