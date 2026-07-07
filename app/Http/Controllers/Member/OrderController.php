<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Outlet;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(): Response
    {
        $orders = Order::with(['items.product', 'outlet'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        $outlets = Outlet::where('is_active', true)->get();

        return inertia('member/orders/Index', [
            'orders' => $orders,
            'outlets' => $outlets,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'outlet_id' => 'required|exists:outlets,id',
            'payment_method' => 'required|in:cash,qris,transfer',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        $order = DB::transaction(function () use ($validated) {
            $order = Order::create([
                'user_id' => auth()->id(),
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

            return $order;
        });

        // Send notification
        $member = auth()->user()->member;
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

        return redirect()->route('member.orders.index')
            ->with('success', 'Pesanan berhasil dibuat!');
    }
}
