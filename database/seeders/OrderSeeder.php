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

        $statusWeights = ['completed', 'completed', 'completed', 'completed', 'processing', 'processing', 'pending', 'cancelled'];

        // Trend multipliers per outlet to simulate growth/decline over 30 days
        // Outlet 0: stable high, 1: growing, 2: seasonal spike mid-month, 3: declining
        $trends = [
            fn (int $day) => 1.0 + ($day / 30) * 0.5,         // growing
            fn (int $day) => 1.2 - ($day / 30) * 0.4,         // declining
            fn (int $day) => $day > 10 && $day < 20 ? 1.6 : 0.8, // spike mid-month
            fn (int $day) => 1.0,                               // stable
        ];

        DB::transaction(function () use ($members, $outlets, $products, $statusWeights, $trends) {
            $outletIndex = 0;

            foreach ($outlets as $outlet) {
                $trend = $trends[$outletIndex % count($trends)];
                $outletIndex++;

                // Generate orders for each of the 30 days
                for ($day = 0; $day < 30; $day++) {
                    $multiplier = $trend($day);
                    $baseCount = (int) round(3 * $multiplier);
                    // Add randomness: ±1
                    $orderCount = max(1, $baseCount + random_int(-1, 1));

                    for ($i = 0; $i < $orderCount; $i++) {
                        $member = $members->random();
                        $status = $statusWeights[array_rand($statusWeights)];
                        $createdAt = now()->subDays(30 - $day)->setTime(random_int(8, 22), random_int(0, 59));

                        $itemCount = random_int(1, 3);
                        $selectedProducts = $products->random(min($itemCount, $products->count()));

                        if ($selectedProducts instanceof Product) {
                            $selectedProducts = collect([$selectedProducts]);
                        }

                        $totalAmount = 0;
                        $items = [];

                        foreach ($selectedProducts as $product) {
                            $quantity = random_int(1, 3);
                            $price = $product->current_price;
                            $subtotal = $price * $quantity;
                            $totalAmount += $subtotal;

                            $items[] = [
                                'product_id' => $product->id,
                                'quantity' => $quantity,
                                'price' => $price,
                                'subtotal' => $subtotal,
                            ];
                        }

                        $order = Order::create([
                            'user_id' => $member->id,
                            'outlet_id' => $outlet->id,
                            'status' => $status,
                            'total_amount' => $totalAmount,
                            'notes' => $status === 'cancelled' ? 'Stok habis' : null,
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt,
                        ]);

                        foreach ($items as $item) {
                            $item['order_id'] = $order->id;
                            OrderItem::create($item);
                        }
                    }
                }
            }
        });
    }
}
