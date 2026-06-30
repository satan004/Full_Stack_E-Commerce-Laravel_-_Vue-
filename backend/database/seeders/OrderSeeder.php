<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('is_admin', false)->get();
        $products = Product::all();

        if ($users->isEmpty() || $products->isEmpty()) {
            return;
        }

        $statuses = ['pending', 'completed', 'cancelled'];
        $paymentMethods = ['credit_card', 'paypal', 'bank_transfer', 'cash_on_delivery'];

        // Create 20 orders with various statuses
        for ($i = 0; $i < 20; $i++) {
            $user = $users->random();
            $status = $statuses[array_rand($statuses)];
            $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
            
            // Random date within last 30 days
            $orderDate = now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59));
            
            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'status' => $status,
                'subtotal' => 0,
                'total' => 0,
                'shipping_address' => $user->address,
                'payment_method' => $paymentMethod,
                'notes' => $i % 3 === 0 ? 'Please deliver before 5 PM' : null,
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);

            // Add 1-5 items to each order
            $itemCount = rand(1, 5);
            $orderProducts = $products->random($itemCount);
            
            $subtotal = 0;
            
            foreach ($orderProducts as $product) {
                $quantity = rand(1, 3);
                $unitPrice = $product->price;
                $total = $unitPrice * $quantity;
                $subtotal += $total;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'total' => $total,
                ]);
            }

            // Update order totals
            $order->update([
                'subtotal' => $subtotal,
                'total' => $subtotal + (rand(0, 1) ? 10 : 0), // Add shipping fee sometimes
            ]);
        }
    }
}