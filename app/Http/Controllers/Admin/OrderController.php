<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Order;
use App\Models\PointTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(): Response
    {
        $orders = Order::with(['user', 'items.product'])
            ->latest()
            ->get();

        return inertia('admin/orders/Index', [
            'orders' => $orders,
        ]);
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled',
        ]);

        $oldStatus = $order->status;

        DB::transaction(function () use ($order, $validated, $oldStatus) {
            $order->update($validated);

            $member = $order->user?->member;
            if (! $member) {
                return;
            }

            // Tambah poin saat order completed (hanya transisi PERTAMA ke completed)
            if ($validated['status'] === 'completed' && $oldStatus !== 'completed') {
                $totalPoints = 0;
                foreach ($order->items as $item) {
                    $product = $item->product;
                    if ($product && $product->points_earned > 0) {
                        $itemPoints = $product->points_earned * $item->quantity;
                        $totalPoints += $itemPoints;
                    }
                }

                if ($totalPoints > 0) {
                    $member->increment('total_points', $totalPoints);

                    PointTransaction::create([
                        'member_id' => $member->id,
                        'type' => 'earn',
                        'amount' => $totalPoints,
                        'reference_type' => Order::class,
                        'reference_id' => $order->id,
                        'note' => 'Poin dari pesanan #'.$order->id,
                    ]);
                }
            }

            // Kirim notifikasi ke member
            $statusLabels = [
                'pending' => 'Menunggu',
                'processing' => 'Sedang Diproses',
                'completed' => 'Selesai',
                'cancelled' => 'Dibatalkan',
            ];
            $statusLabel = $statusLabels[$validated['status']] ?? $validated['status'];

            $notifBody = 'Pesanan #'.$order->id.' berstatus: '.$statusLabel;

            if ($validated['status'] === 'completed' && isset($totalPoints) && $totalPoints > 0) {
                $notifBody .= '. Kamu mendapatkan +'.$totalPoints.' poin!';
            }

            Notification::create([
                'member_id' => $member->id,
                'type' => 'order_status',
                'title' => 'Status Pesanan Diperbarui',
                'body' => $notifBody,
                'data' => [
                    'order_id' => $order->id,
                    'status' => $validated['status'],
                ],
            ]);
        });

        return back()->with('success', 'Status pesanan diperbarui.');
    }
}
