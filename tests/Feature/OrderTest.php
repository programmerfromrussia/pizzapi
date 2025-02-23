<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Database\Factories\OrderItemFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $cart;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->cart = Cart::factory()->create(['user_id' => $this->user->id]);
        $this->product = Product::factory()->create(['price' => 100]);
    }

    #[Test]
    public function test_store_order()
    {
        CartItem::factory()->create([
            'cart_id' => $this->cart->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->user, 'api')->postJson('/api/orders', [
            'address' => '123 Main St',
            'city' => 'New York',
            'country' => 'USA',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'order' => [
                         'id',
                         'status',
                         'total_price',
                         'location',
                         'items',
                         'created_at',
                         'updated_at',
                     ],
                 ]);

        $this->assertDatabaseHas('orders', ['cart_id' => $this->cart->id]);
        $this->assertDatabaseHas('order_items', ['product_id' => $this->product->id]);
        $this->assertDatabaseHas('locations', ['address' => '123 Main St']);
    }

    #[Test]
    public function test_index_orders()
    {
        $order = Order::factory()->create(['cart_id' => $this->cart->id]);
        Location::factory()->create(['order_id' => $order->id]);
        OrderItemFactory::new()->create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'price' => $this->product->price,
        ]);

        $response = $this->actingAs($this->user, 'api')->getJson('/api/orders');

        \Log::info('Response Data: ', $response->json());

        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'status',
                            'total_price',
                            'location' => [
                                'address',
                                'city',
                                'country',
                            ],
                            'items' => [
                                '*' => [
                                    'id',
                                    'product_name',
                                    'quantity',
                                    'price',
                                ],
                            ],
                            'created_at',
                            'updated_at',
                        ],
                    ],
                 ]);
    }

    #[Test]
    public function test_show_order()
    {
        $order = Order::factory()->create(['cart_id' => $this->cart->id]);
        Location::factory()->create(['order_id' => $order->id]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'price' => $this->product->price,
        ]);

        $response = $this->actingAs($this->user, 'api')->getJson("/api/orders/{$order->id}");

        \Log::info('Response show', $response->json());

        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'data' => [
                        'id',
                        'status',
                        'total_price',
                        'location' => [
                            'address',
                            'city',
                            'country',
                        ],
                        'items' => [
                            '*' => [
                                'id',
                                'product_name',
                                'quantity',
                                'price',
                            ],
                        ],
                        'created_at',
                        'updated_at',
                    ],
                 ]);
    }

    #[Test]
    public function test_destroy_order()
    {
        $order = Order::factory()->create(['cart_id' => $this->cart->id]);
        Location::factory()->create(['order_id' => $order->id]);

        $response = $this->actingAs($this->user, 'api')->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Order cancelled successfully']);

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => OrderStatus::CANCELLED]);
    }

    #[Test]
    public function test_update_order_location()
    {
        $order = Order::factory()->create(['cart_id' => $this->cart->id]);
        $location = Location::factory()->create(['order_id' => $order->id]);

        $response = $this->actingAs($this->user, 'api')->putJson("/api/orders/{$order->id}", [
            'address' => '456 Elm St',
            'city' => 'Los Angeles',
            'country' => 'USA',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'order' => [
                         'id',
                         'status',
                         'total_price',
                         'location',
                         'items',
                         'created_at',
                         'updated_at',
                     ],
                 ]);

        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'address' => '456 Elm St',
            'city' => 'Los Angeles',
            'country' => 'USA',
        ]);
    }
}
