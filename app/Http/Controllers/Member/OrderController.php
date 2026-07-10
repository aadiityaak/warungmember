<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\DepositTransaction;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Outlet;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(): Response
    {
        $outlets = Outlet::where('is_active', true)->get();
        $lastOutletId = auth()->user()->member?->last_outlet_id;
        $depositBalance = auth()->user()->member?->deposit_balance ?? 0;

        return inertia('member/orders/Index', [
            'outlets' => $outlets,
            'lastOutletId' => $lastOutletId,
            'depositBalance' => $depositBalance,
        ]);
    }

    public function history(): Response
    {
        $orders = Order::with(['items.product', 'outlet'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return inertia('member/orders/History', [
            'orders' => $orders,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'outlet_id' => 'required|exists:outlets,id',
            'payment_method' => 'required|in:cash,qris,transfer,deposit',
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

            if ($validated['payment_method'] === 'deposit') {
                $member = auth()->user()->member;
                if (! $member || $member->deposit_balance < $total) {
                    throw ValidationException::withMessages([
                        'payment_method' => 'Saldo deposit tidak cukup. Saldo: Rp'.number_format($member?->deposit_balance ?? 0, 0, ',', '.').', Total: Rp'.number_format($total, 0, ',', '.'),
                    ]);
                }
                $member->decrement('deposit_balance', $total);
                DepositTransaction::create([
                    'member_id' => $member->id,
                    'type' => 'payment',
                    'amount' => $total,
                    'reference_type' => Order::class,
                    'reference_id' => $order->id,
                    'note' => 'Pembayaran pesanan #'.$order->id,
                ]);
                $order->update([
                    'paid_amount' => $total,
                    'change' => 0,
                ]);
            }

            return $order;
        });

        // Persist outlet selection
        $member = auth()->user()->member;
        if ($member) {
            $member->update(['last_outlet_id' => $validated['outlet_id']]);

            // Send notification
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
