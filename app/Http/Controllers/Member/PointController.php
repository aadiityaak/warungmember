<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Inertia\Response;

class PointController extends Controller
{
    public function __invoke(): Response
    {
        $member = request()->user()->member;

        $transactions = $member?->pointTransactions()
            ->latest('created_at')
            ->paginate(20);

        return inertia('member/points/Index', [
            'transactions' => $transactions,
            'totalPoints' => $member?->total_points ?? 0,
        ]);
    }
}
