<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\Order;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::with('user.member')->get();

        if ($orders->isEmpty()) {
            return;
        }

        $statusLabels = [
            'pending' => 'Menunggu',
            'processing' => 'Sedang Diproses',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];

        $notifications = [];

        foreach ($orders as $order) {
            $member = $order->user?->member;

            if (! $member) {
                continue;
            }

            // Notifikasi "Pesanan Baru" - selalu dibuat saat order dibuat
            $notifications[] = [
                'member_id' => $member->id,
                'type' => 'order',
                'title' => 'Pesanan Baru Diterima',
                'body' => 'Pesanan #'.$order->id.' sebesar Rp'.number_format($order->total_amount, 0, ',', '.').' sedang diproses.',
                'data' => json_encode(['order_id' => $order->id, 'total_amount' => $order->total_amount]),
                'created_at' => $order->created_at,
            ];

            // Notifikasi "Status Pesanan" - hanya jika status bukan pending
            if ($order->status !== 'pending') {
                $statusLabel = $statusLabels[$order->status] ?? $order->status;
                $body = 'Pesanan #'.$order->id.' berstatus: '.$statusLabel;

                if ($order->status === 'completed') {
                    $body .= '. Terima kasih sudah berbelanja!';
                }

                $notifications[] = [
                    'member_id' => $member->id,
                    'type' => 'order_status',
                    'title' => 'Status Pesanan Diperbarui',
                    'body' => $body,
                    'data' => json_encode(['order_id' => $order->id, 'status' => $order->status]),
                    'created_at' => (string) $order->created_at->copy()->addMinutes(random_int(5, 60)),
                ];
            }
        }

        DB::table('notifications')->insert($notifications);
    }
}
