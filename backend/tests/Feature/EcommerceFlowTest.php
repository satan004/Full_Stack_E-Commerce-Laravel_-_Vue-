<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_admin_can_create_product_with_uploaded_image(): void
    {
        Storage::fake('public');

        $admin = User::create([
            'name' => 'Admin Manager',
            'email' => 'admin-upload@example.com',
            'password' => 'password',
            'is_admin' => true,
        ]);

        $category = Category::create([
            'name' => 'Accessories',
            'slug' => 'accessories',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'category_id' => $category->id,
                'name' => 'MagSafe Case',
                'description' => 'Protective phone case.',
                'price' => 19.99,
                'stock' => 25,
                'is_active' => '1',
                'image' => $this->fakePngUpload('case.png'),
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::where('name', 'MagSafe Case')->firstOrFail();

        $this->assertStringStartsWith('products/', $product->image_path);
        Storage::disk('public')->assertExists($product->image_path);
    }

    public function test_admin_api_can_create_product_with_uploaded_image(): void
    {
        Storage::fake('public');

        $admin = User::create([
            'name' => 'API Admin',
            'email' => 'api-admin@example.com',
            'password' => 'password',
            'is_admin' => true,
        ]);

        $category = Category::create([
            'name' => 'Phones',
            'slug' => 'phones',
        ]);

        $token = $this->postJson('/api/login', [
            'email' => 'api-admin@example.com',
            'password' => 'password',
        ])
            ->assertOk()
            ->json('token');

        $response = $this->withToken($token)
            ->post('/api/admin/products', [
                'category_id' => $category->id,
                'name' => 'Pixel Fold Case',
                'description' => 'Slim foldable phone case.',
                'price' => '29.99',
                'stock' => '12',
                'is_active' => '1',
                'image' => $this->fakePngUpload('fold-case.png'),
            ], ['Accept' => 'application/json']);

        $response
            ->assertCreated()
            ->assertJsonPath('name', 'Pixel Fold Case')
            ->assertJsonPath('category.id', $category->id);

        $product = Product::where('name', 'Pixel Fold Case')->firstOrFail();

        $this->assertStringStartsWith('products/', $product->image_path);
        Storage::disk('public')->assertExists($product->image_path);
    }

    public function test_google_callback_returns_frontend_error_when_google_denies_access(): void
    {
        $this->get('/auth/google/callback?error=access_denied')
            ->assertRedirect(config('services.frontend.url').'/login?google_error=Google%20login%20was%20cancelled.');
    }

    public function test_customer_must_buy_product_before_reviewing_it(): void
    {
        $category = Category::create([
            'name' => 'Review Gear',
            'slug' => 'review-gear',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Reviewable Headphones',
            'slug' => 'reviewable-headphones',
            'price' => 79.00,
            'stock' => 5,
            'is_active' => true,
        ]);

        $token = $this->postJson('/api/register', [
            'name' => 'Review Customer',
            'email' => 'review@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->json('token');

        $this->withToken($token)
            ->postJson("/api/products/{$product->id}/reviews", [
                'rating' => 5,
                'comment' => 'Great quality.',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('product');
    }

    public function test_customer_can_review_product_after_checkout(): void
    {
        $category = Category::create([
            'name' => 'Purchased Gear',
            'slug' => 'purchased-gear',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Purchased Speaker',
            'slug' => 'purchased-speaker',
            'price' => 49.00,
            'stock' => 4,
            'is_active' => true,
        ]);

        $token = $this->postJson('/api/register', [
            'name' => 'Buyer Customer',
            'email' => 'buyer@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->json('token');

        $this->withToken($token)
            ->postJson('/api/cart', ['product_id' => $product->id, 'quantity' => 1])
            ->assertCreated();

        $this->withToken($token)
            ->postJson('/api/checkout', [
                'shipping_address' => 'Phnom Penh, Cambodia',
                'payment_method' => 'cash_on_delivery',
            ])
            ->assertCreated();

        $this->withToken($token)
            ->postJson("/api/products/{$product->id}/reviews", [
                'rating' => 4,
                'comment' => 'Works well after delivery.',
            ])
            ->assertCreated()
            ->assertJsonPath('reviews.0.rating', 4)
            ->assertJsonPath('reviews.0.comment', 'Works well after delivery.');

        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'rating' => 4,
            'comment' => 'Works well after delivery.',
        ]);
    }

    public function test_customer_can_update_profile_name_and_avatar(): void
    {
        Storage::fake('public');

        $token = $this->postJson('/api/register', [
            'name' => 'Avatar Customer',
            'email' => 'avatar@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->json('token');

        $response = $this->withToken($token)
            ->post('/api/profile', [
                '_method' => 'PUT',
                'name' => 'Updated Avatar Customer',
                'email' => 'avatar@example.com',
                'phone' => '+855 12 000 000',
                'address' => 'Phnom Penh, Cambodia',
                'avatar' => $this->fakePngUpload('avatar.png'),
            ], ['Accept' => 'application/json']);

        $response
            ->assertOk()
            ->assertJsonPath('name', 'Updated Avatar Customer')
            ->assertJsonPath('phone', '+855 12 000 000');

        $avatarPath = $response->json('avatar_path');

        $this->assertStringStartsWith('avatars/', $avatarPath);
        Storage::disk('public')->assertExists($avatarPath);
    }

    private function fakePngUpload(string $name): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'product-image-');

        file_put_contents(
            $path,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=')
        );

        return new UploadedFile($path, $name, 'image/png', null, true);
    }
}
