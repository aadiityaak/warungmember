<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\RewardRedemption;
use App\Models\User;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $memberModel = Member::class;

        return inertia('admin/Dashboard', [
            'stats' => [
                'total_members' => User::where('role', 'member')->count(),
                'total_points' => $memberModel::sum('total_points'),
                'total_deposit' => $memberModel::sum('deposit_balance'),
                'vouchers_redeemed' => RewardRedemption::count(),
            ],
        ]);
    }
}
