<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Order;
use App\Models\PointTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(): Response
    {
        $orders = Order::with(['user', 'items.product'])
            ->when(auth()->user()?->role !== 'admin', function ($query) {
                $outlet = auth()->user()?->outlet;
                if ($outlet) {
                    $query->where('outlet_id', $outlet->id);
                }
            })
            ->latest()
            ->get();

        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return inertia('admin/orders/Index', [
            'orders' => $orders,
            'products' => $products,
        ]);
    }

    public function update(Request $request, Order $order)
    {
        if (auth()->user()?->role !== 'admin') {
            $outlet = auth()->user()?->outlet;
            if (! $outlet || $order->outlet_id !== $outlet->id) {
                abort(403);
            }
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled',
            'notes' => 'nullable|string|max:500',
            'items' => 'nullable|array',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
        ]);

        $oldStatus = $order->status;

        DB::transaction(function () use ($order, $validated, $oldStatus) {
            $orderData = ['status' => $validated['status']];

            if (array_key_exists('notes', $validated)) {
                $orderData['notes'] = $validated['notes'];
            }

            $order->update($orderData);

            // Update items if provided
            if (isset($validated['items'])) {
                $order->items()->delete();

                $totalAmount = 0;
                foreach ($validated['items'] as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    $price = $product->current_price;
                    $subtotal = $price * $item['quantity'];

                    $order->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $price,
                        'subtotal' => $subtotal,
                    ]);

                    $totalAmount += $subtotal;
                }

                $order->update(['total_amount' => $totalAmount]);
            }

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

        return back()->with('success', 'Pesanan berhasil diperbarui.');
    }

    public function destroy(Order $order)
    {
        if (auth()->user()?->role !== 'admin') {
            $outlet = auth()->user()?->outlet;
            if (! $outlet || $order->outlet_id !== $outlet->id) {
                abort(403);
            }
        }

        $order->delete();

        return back()->with('success', 'Pesanan berhasil dihapus.');
    }
}
