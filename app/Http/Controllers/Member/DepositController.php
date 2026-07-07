<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Inertia\Response;

class DepositController extends Controller
{
    public function __invoke(): Response
    {
        $member = request()->user()->member;

        $transactions = $member?->depositTransactions()
            ->latest('created_at')
            ->paginate(20);

        return inertia('member/deposits/Index', [
            'transactions' => $transactions,
            'balance' => $member?->deposit_balance ?? 0,
        ]);
    }
}
