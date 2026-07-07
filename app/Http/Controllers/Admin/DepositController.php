<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Response;

class DepositController extends Controller
{
    public function index(): Response
    {
        $members = Member::with('user:id,name,email')->latest()->get();

        return inertia('admin/deposits/Index', [
            'members' => $members,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'amount' => 'required|integer|min:1000',
        ]);

        $member = Member::findOrFail($validated['member_id']);

        DB::transaction(function () use ($member, $validated) {
            $member->increment('deposit_balance', $validated['amount']);

            $member->depositTransactions()->create([
                'type' => 'topup',
                'amount' => $validated['amount'],
                'note' => 'Deposit via kasir',
            ]);
        });

        return redirect()->route('admin.deposits.index')
            ->with('toast', ['type' => 'success', 'message' => "Deposit Rp {$validated['amount']} berhasil ditambahkan untuk {$member->user->name}."]);
    }

    public function history(Member $member): Response
    {
        $transactions = $member->depositTransactions()
            ->latest('created_at')
            ->paginate(20);

        return inertia('admin/deposits/History', [
            'member' => $member->load('user:id,name,email'),
            'transactions' => $transactions,
        ]);
    }
}
