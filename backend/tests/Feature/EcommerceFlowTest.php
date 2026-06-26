<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EcommerceFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_catalog_can_be_filtered(): void
    {
        $category = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
        ]);

        Product::create([
            'category_id' => $category->id,
            'name' => 'Nimbus Wireless Headphones',
            'slug' => 'nimbus-wireless-headphones',
            'description' => 'Noise reducing audio.',
            'price' => 129.00,
            'stock' => 10,
            'is_active' => true,
        ]);

        $this->getJson('/api/products?search=Nimbus&category=electronics')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Nimbus Wireless Headphones');
    }

    public function test_customer_can_register_use_cart_and_checkout(): void
    {
        $category = Category::create([
            'name' => 'Home Living',
            'slug' => 'home-living',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Luma Desk Lamp',
            'slug' => 'luma-desk-lamp',
            'description' => 'Dimmable LED desk lamp.',
            'price' => 39.00,
            'stock' => 5,
            'is_active' => true,
        ]);

        $token = $this->postJson('/api/register', [
            'name' => 'Demo Customer',
            'email' => 'demo@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
            ->assertCreated()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']])
            ->json('token');

        $this->withToken($token)
            ->postJson('/api/wishlist', ['product_id' => $product->id])
            ->assertCreated()
            ->assertJsonPath('product.name', 'Luma Desk Lamp');

        $this->withToken($token)
            ->postJson('/api/cart', ['product_id' => $product->id, 'quantity' => 2])
            ->assertCreated()
            ->assertJsonPath('count', 2)
            ->assertJsonPath('subtotal', 78);

        $this->withToken($token)
            ->postJson('/api/checkout', [
                'shipping_address' => 'Phnom Penh, Cambodia',
                'payment_method' => 'cash_on_delivery',
            ])
            ->assertCreated()
            ->assertJsonPath('total', '78.00')
            ->assertJsonPath('items.0.quantity', 2);

        $this->assertDatabaseHas('orders', [
            'shipping_address' => 'Phnom Penh, Cambodia',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('order_items', [
            'product_name' => 'Luma Desk Lamp',
            'quantity' => 2,
        ]);
        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_admin_can_login_to_dashboard(): void
    {
        $admin = User::create([
            'name' => 'Admin Manager',
            'email' => 'admin@gmail.com',
            'password' => '1234567',
            'is_admin' => true,
        ]);

        $this->post('/admin/login', [
            'email' => 'admin@gmail.com',
            'password' => '1234567',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);

        $this->get('/admin')
            ->assertOk()
            ->assertSee('Dashboard');
    }
}
