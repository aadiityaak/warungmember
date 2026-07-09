<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Outlet;
use App\Models\PointTransaction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(): Response
    {
        $outlet = auth()->user()?->outlet;

        $orders = Order::with(['user', 'items.product', 'outlet:id,name'])
            ->when(auth()->user()?->role !== 'admin', function ($query) use ($outlet) {
                if ($outlet) {
                    $query->where('outlet_id', $outlet->id);
                }
            })
            ->latest()
            ->paginate(15);

        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'image', 'price', 'discount_price', 'discount_end_at']);

        $members = User::where('role', 'member')
            ->with('member:id,user_id,member_code')
            ->orderBy('name')
            ->get(['id', 'name', 'avatar']);

        $outlets = Outlet::when(auth()->user()?->role !== 'admin', function ($query) use ($outlet) {
            if ($outlet) {
                $query->where('id', $outlet->id);
            }
        })
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return inertia('admin/orders/Index', [
            'orders' => $orders,
            'products' => $products,
            'members' => $members,
            'outlets' => $outlets,
        ]);
    }

    public function receipt(Order $order): Response
    {
        $order->load(['user', 'items.product', 'outlet']);

        return inertia('admin/orders/Receipt', [
            'order' => $order,
        ]);
    }

    public function store(Request $request)
    {
        $outlet = auth()->user()?->outlet;

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'outlet_id' => 'required|exists:outlets,id',
            'payment_method' => 'required|in:cash,qris,transfer',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'paid_amount' => 'nullable|integer|min:0|required_if:payment_method,cash',
            'notes' => 'nullable|string|max:500',
        ]);

        // Kasir hanya bisa bikin order di outlet sendiri
        if (auth()->user()?->role !== 'admin' && $outlet) {
            if ((int) $validated['outlet_id'] !== $outlet->id) {
                abort(403);
            }
        }

        $order = DB::transaction(function () use ($validated) {
            $order = Order::create([
                'user_id' => $validated['user_id'],
                'outlet_id' => $validated['outlet_id'],
                'payment_method' => $validated['payment_method'],
                'status' => 'pending',
                'total_amount' => 0,
                'notes' => $validated['notes'] ?? null,
            ]);

            $total = 0;

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $price = $product->current_price;
                $subtotal = $price * $item['quantity'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $price,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            $order->update(['total_amount' => $total]);

            if ($validated['payment_method'] === 'cash' && isset($validated['paid_amount'])) {
                $paidAmount = (int) $validated['paid_amount'];
                $order->update([
                    'paid_amount' => $paidAmount,
                    'change' => max(0, $paidAmount - $total),
                ]);
            }

            // Kirim notifikasi ke member
            $member = $order->user?->member;
            if ($member) {
                Notification::create([
                    'member_id' => $member->id,
                    'type' => 'order',
                    'title' => 'Pesanan Baru Diterima',
                    'body' => 'Pesanan #'.$order->id.' sebesar Rp'.number_format($order->total_amount, 0, ',', '.').' sedang diproses.',
                    'data' => [
                        'order_id' => $order->id,
                        'total_amount' => $order->total_amount,
                    ],
                ]);
            }

            return $order;
        });

        return back()->with('success', 'Pesanan #'.$order->id.' berhasil dibuat.');
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
