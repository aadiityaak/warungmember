<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\RewardRedemption;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $user = auth()->user();
        $isAdmin = $user?->role === 'admin';
        $outlet = $user?->outlet;

        $memberModel = Member::class;

        $orderQuery = Order::query();
        if (! $isAdmin && $outlet) {
            $orderQuery->where('outlet_id', $outlet->id);
        }

        $totalOrders = (clone $orderQuery)->count();
        $completedOrders = (clone $orderQuery)->where('status', 'completed')->count();
        $pendingOrders = (clone $orderQuery)->where('status', 'pending')->count();

        $recentOrders = (clone $orderQuery)
            ->with(['user:id,name', 'outlet:id,name'])
            ->latest()
            ->take(5)
            ->get()
            ->map(fn (Order $o) => [
                'id' => $o->id,
                'user_name' => $o->user?->name,
                'outlet_name' => $o->outlet?->name,
                'status' => $o->status,
                'total_amount' => $o->total_amount,
                'created_at' => $o->created_at?->toIso8601String(),
            ]);

        // Chart: daily order count per outlet for last 30 days
        $labels = collect(range(29, 0))->map(fn (int $d) => now()->subDays($d)->format('Y-m-d'))->values();

        $chartQuery = Order::query();
        if (! $isAdmin && $outlet) {
            $chartQuery->where('outlet_id', $outlet->id);
        }

        $rawData = $chartQuery
            ->where('created_at', '>=', now()->subDays(30))
            ->select(
                DB::raw('DATE(created_at) as date'),
                'outlet_id',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date', 'outlet_id')
            ->orderBy('date')
            ->get();

        $outletsForChart = $isAdmin
            ? Outlet::where('is_active', true)->get(['id', 'name'])
            : collect([$outlet?->only(['id', 'name'])])->filter();

        $chartSeries = $outletsForChart->map(function ($o) use ($labels, $rawData) {
            $rowData = $labels->map(function (string $date) use ($o, $rawData) {
                $outletId = is_array($o) ? $o['id'] : $o->id;
                $matched = $rawData->first(fn ($r) => $r->date === $date && $r->outlet_id === $outletId);

                return $matched ? (int) $matched->count : 0;
            })->values();

            return ['name' => is_array($o) ? $o['name'] : $o->name, 'data' => $rowData];
        })->values();

        $chart = [
            'labels' => $labels,
            'series' => $chartSeries,
        ];

        return inertia('admin/Dashboard', [
            'stats' => [
                'total_members' => User::where('role', 'member')->count(),
                'total_outlets' => $isAdmin ? Outlet::count() : 1,
                'total_products' => Product::count(),
                'total_vouchers' => Voucher::where('is_active', true)->count(),
                'total_points' => $memberModel::sum('total_points'),
                'total_deposit' => $memberModel::sum('deposit_balance'),
                'vouchers_redeemed' => RewardRedemption::count(),
                'total_orders' => $totalOrders,
                'completed_orders' => $completedOrders,
                'pending_orders' => $pendingOrders,
            ],
            'recent_orders' => $recentOrders,
            'chart' => $chart,
        ]);
    }
}
