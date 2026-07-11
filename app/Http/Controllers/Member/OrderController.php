<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Jobs\SendPushNotification;
use App\Models\DepositTransaction;
use App\Models\MemberVoucher;
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
        $member = auth()->user()->member;
        $lastOutletId = $member?->last_outlet_id;
        $depositBalance = $member?->deposit_balance ?? 0;

        $activeVouchers = $member?->memberVouchers()
            ->with('voucher')
            ->where('status', 'active')
            ->whereHas('voucher', fn ($q) => $q->where('is_active', true))
            ->get() ?? [];

        return inertia('member/orders/Index', [
            'outlets' => $outlets,
            'lastOutletId' => $lastOutletId,
            'depositBalance' => $depositBalance,
            'activeVouchers' => $activeVouchers,
        ]);
    }

    public function history(): Response
    {
        $orders = Order::with(['items.product', 'outlet', 'memberVoucher.voucher'])
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
            'member_voucher_id' => 'nullable|exists:member_vouchers,id',
        ]);

        $order = DB::transaction(function () use ($validated, $request) {
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

            // Apply voucher
            $discount = 0;
            if (! empty($validated['member_voucher_id'])) {
                $memberVoucher = MemberVoucher::with('voucher')
                    ->where('id', $validated['member_voucher_id'])
                    ->where('status', 'active')
                    ->first();

                if (! $memberVoucher || ! $memberVoucher->voucher || ! $memberVoucher->voucher->is_active) {
                    throw ValidationException::withMessages([
                        'member_voucher_id' => 'Voucher tidak valid atau sudah digunakan.',
                    ]);
                }

                $voucher = $memberVoucher->voucher;

                // Pastikan voucher milik member ini
                $member = auth()->user()->member;
                if (! $member || $memberVoucher->member_id !== $member->id) {
                    throw ValidationException::withMessages([
                        'member_voucher_id' => 'Voucher bukan milik kamu.',
                    ]);
                }

                // Validasi min_purchase
                if ($voucher->min_purchase > 0 && $total < $voucher->min_purchase) {
                    throw ValidationException::withMessages([
                        'member_voucher_id' => 'Minimal belanja Rp '.number_format($voucher->min_purchase, 0, ',', '.').' untuk menggunakan voucher ini.',
                    ]);
                }

                // Hitung diskon
                if ($voucher->discount_type === 'percent') {
                    $discount = (int) round($total * $voucher->discount_value / 100);
                    if ($voucher->max_discount && $discount > $voucher->max_discount) {
                        $discount = $voucher->max_discount;
                    }
                } else {
                    $discount = $voucher->discount_value;
                }

                // Tandai voucher sudah terpakai
                $memberVoucher->update(['status' => 'used']);

                $order->update([
                    'discount_amount' => $discount,
                    'member_voucher_id' => $memberVoucher->id,
                ]);
            }

            $finalTotal = $total - $discount;
            $order->update(['total_amount' => $finalTotal]);

            if ($validated['payment_method'] === 'deposit') {
                $member = auth()->user()->member;
                if (! $member || $member->deposit_balance < $finalTotal) {
                    throw ValidationException::withMessages([
                        'payment_method' => 'Saldo deposit tidak cukup. Saldo: Rp'.number_format($member?->deposit_balance ?? 0, 0, ',', '.').', Total: Rp'.number_format($finalTotal, 0, ',', '.'),
                    ]);
                }
                $member->decrement('deposit_balance', $finalTotal);
                DepositTransaction::create([
                    'member_id' => $member->id,
                    'type' => 'payment',
                    'amount' => $finalTotal,
                    'reference_type' => Order::class,
                    'reference_id' => $order->id,
                    'note' => 'Pembayaran pesanan #'.$order->id,
                ]);
                $order->update([
                    'paid_amount' => $finalTotal,
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

            // Send push notification via ntfy + web push
            dispatch(new SendPushNotification($member, [
                'title' => 'Pesanan Baru Diterima',
                'body' => 'Pesanan #'.$order->id.' sebesar Rp'.number_format($order->total_amount, 0, ',', '.').' sedang diproses.',
                'type' => 'order',
                'url' => route('member.notifications'),
            ]));
        }

        return redirect()->route('member.orders.index')
            ->with('success', 'Pesanan berhasil dibuat!');
    }
}
