<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Response;

class RewardController extends Controller
{
    public function index(): Response
    {
        $member = request()->user()->member;

        return inertia('member/rewards/Index', [
            'rewards' => Reward::where('is_active', true)->get(),
            'memberPoints' => $member?->total_points ?? 0,
        ]);
    }

    public function redeem(Request $request, Reward $reward)
    {
        $member = $request->user()->member;

        if (! $member) {
            return back()->with('toast', ['type' => 'error', 'message' => 'Akun member tidak ditemukan.']);
        }

        if (! $reward->is_active) {
            return back()->with('toast', ['type' => 'error', 'message' => 'Reward tidak tersedia.']);
        }

        if ($reward->stock !== null && $reward->stock <= 0) {
            return back()->with('toast', ['type' => 'error', 'message' => 'Stok reward habis.']);
        }

        if ($member->total_points < $reward->points_required) {
            return back()->with('toast', ['type' => 'error', 'message' => 'Poin tidak mencukupi.']);
        }

        DB::transaction(function () use ($member, $reward) {
            $member->decrement('total_points', $reward->points_required);

            if ($reward->stock !== null) {
                $reward->decrement('stock');
            }

            $member->pointTransactions()->create([
                'type' => 'redeem',
                'amount' => $reward->points_required,
                'note' => "Tukar reward: {$reward->name}",
            ]);

            $member->rewardRedemptions()->create([
                'reward_id' => $reward->id,
                'points_spent' => $reward->points_required,
                'status' => 'completed',
                'redeemed_at' => now(),
            ]);
        });

        return back()->with('toast', ['type' => 'success', 'message' => "Berhasil menukar {$reward->name}!"]);
    }
}
