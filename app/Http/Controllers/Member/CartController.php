<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(): JsonResponse
    {
        $items = CartItem::with('product')
            ->where('user_id', auth()->id())
            ->get()
            ->map(fn (CartItem $item) => [
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'price' => $item->product->price,
                'current_price' => $item->product->current_price,
                'image' => $item->product->image,
                'quantity' => $item->quantity,
            ]);

        return response()->json(['items' => $items]);
    }

    public function sync(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $userId = auth()->id();

        // Replace all cart items atomically
        CartItem::where('user_id', $userId)->delete();

        foreach ($validated['items'] ?? [] as $item) {
            CartItem::create([
                'user_id' => $userId,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
            ]);
        }

        return response()->json(['ok' => true]);
    }
}
