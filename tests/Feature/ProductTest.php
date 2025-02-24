<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;


class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);

        $this->user = User::factory()->create(['is_admin' => false]);
    }

    #[Test]
    public function admin_can_create_products(): void
    {
        $response = $this->actingAs($this->admin, 'api')->postJson('/api/admin/products', [
            'name' => 'Test Product',
            'description' => 'This is a test product',
            'price' => 19.99,
            'category' => 'Test Category',
            'image' => 'https://example.com/image.jpg',
            'is_available' => true,
        ]);

        $response->assertStatus(201)
                ->assertJson(['name' => 'Test Product']);

        $this->assertDatabaseHas('products', ['name' => 'Test Product']);
    }

    #[Test]
    public function non_admin_cannot_create_a_product()
    {
        $response = $this->actingAs($this->user, 'api')->postJson('/api/admin/products', [
            'name' => 'Test Product',
            'description' => 'This is a test product',
            'price' => 19.99,
            'category' => 'Test Category',
            'image' => 'https://example.com/image.jpg',
            'is_available' => true,
        ]);

        $response->assertStatus(403); // Forbidden
    }

    #[Test]
    public function admin_can_update_a_product()
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->admin, 'api')->putJson("/api/admin/products/{$product->id}", [
            'name' => 'Updated Product',
            'price' => 29.99,
            'category' => 'Пицца',
        ]);

        $response->assertStatus(200)
                ->assertJson(['name' => 'Updated Product']);

        $this->assertDatabaseHas('products', ['name' => 'Updated Product']);
    }

    #[Test]
    public function non_admin_cannot_update_a_product()
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->user, 'api')->putJson("/api/admin/products/{$product->id}", [
            'name' => 'Updated Product',
            'price' => 29.99,
            'category' => 'Пицца',
        ]);
        $response->assertStatus(403);
    }

    #[Test]
    public function non_admin_cannot_delete_a_product()
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->user, 'api')->deleteJson("/api/admin/products/{$product->id}");

        $response->assertStatus(403); // Forbidden
    }

    #[Test]
    public function anyone_can_view_all_products()
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/product');

        $response->assertStatus(200)
                ->assertJsonCount(3);
    }
}
