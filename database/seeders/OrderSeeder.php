<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $members = User::where('role', 'member')->get();
        $outlets = Outlet::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();

        if ($members->isEmpty() || $outlets->isEmpty() || $products->isEmpty()) {
            return;
        }

        $statuses = ['pending', 'processing', 'completed', 'cancelled'];
        // weighted: more completed orders
        $statusWeights = ['completed', 'completed', 'completed', 'completed', 'processing', 'processing', 'pending', 'cancelled'];

        $orders = [];

        // Generate ~30 orders spread across members and outlets
        foreach ($members as $member) {
            $orderCount = random_int(1, 3);

            for ($i = 0; $i < $orderCount; $i++) {
                $outlet = $outlets->random();
                $status = $statusWeights[array_rand($statusWeights)];
                $createdAt = now()->subDays(random_int(0, 30))->addHours(random_int(0, 23));

                $orders[] = [
                    'user_id' => $member->id,
                    'outlet_id' => $outlet->id,
                    'status' => $status,
                    'total_amount' => 0, // will be calculated
                    'notes' => $status === 'cancelled' ? 'Stok habis' : null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];
            }
        }

        // Shuffle untuk variasi
        shuffle($orders);

        // Limit to ~30 orders
        $orders = array_slice($orders, 0, 30);

        DB::transaction(function () use ($orders, $products) {
            foreach ($orders as $orderData) {
                $order = Order::create($orderData);

                // Add 1-3 random items
                $itemCount = random_int(1, 3);
                $selectedProducts = $products->random(min($itemCount, $products->count()));

                if ($selectedProducts instanceof Product) {
                    $selectedProducts = collect([$selectedProducts]);
                }

                $totalAmount = 0;

                foreach ($selectedProducts as $product) {
                    $quantity = random_int(1, 3);
                    $price = $product->current_price;
                    $subtotal = $price * $quantity;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $price,
                        'subtotal' => $subtotal,
                    ]);

                    $totalAmount += $subtotal;
                }

                $order->update(['total_amount' => $totalAmount]);
            }
        });
    }
}
