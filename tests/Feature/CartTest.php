<?php

namespace Tests\Feature;

use App\Enums\CartLimit;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    #[Test]
    public function it_returns_empty_cart_if_no_items_exist()
    {
        $this->actingAs($this->user, 'api')
             ->getJson('/api/cart')
             ->assertStatus(status: 200)
             ->assertJson(['message' => 'No items found -_-']);
    }

    #[Test]
    public function it_returns_cart_items_if_cart_exists()
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        $cartItem = CartItem::factory()->create(['cart_id' => $cart->id]);

        $this->actingAs($this->user, 'api')
             ->getJson('/api/cart')
             ->assertStatus(200)
             ->assertJson([$cartItem->toArray()]);
    }

    #[Test]
    public function it_adds_product_to_cart()
    {
        $product = Product::factory()->create(['category' => 'Пицца']);

        $response = $this->actingAs($this->user, 'api')
                         ->postJson('/api/cart', [
                             'product_id' => $product->id,
                             'quantity' => 2,
                             'price' => $product->price,
                         ])
                         ->assertStatus(201);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    #[Test]
    public function it_fails_to_add_more_than_limit_for_category()
    {
        $product = Product::factory()->create(['category' => 'Пицца']);

        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => CartLimit::PIZZA->value,
        ]);

        $this->actingAs($this->user, 'api')
             ->postJson('/api/cart', [
                 'product_id' => $product->id,
                 'quantity' => 1,
                 'price' => $product->price,
             ])
             ->assertStatus(400)
             ->assertJson(['message' => 'Cannot add more than ' . CartLimit::PIZZA->value . ' items of category Пицца.']);
    }

    #[Test]
    public function it_updates_cart_item_quantity()
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        $cartItem = CartItem::factory()->create(['cart_id' => $cart->id]);

        $this->actingAs($this->user, 'api')
             ->putJson("/api/cart/{$cartItem->id}", ['quantity' => 5])
             ->assertStatus(200)
             ->assertJson(['quantity' => 5]);

        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 5,
        ]);
    }

    #[Test]
    public function it_fails_to_update_if_quantity_exceeds_limit()
    {
        $product = Product::factory()->create(['category' => 'Пицца']);
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => CartLimit::PIZZA->value,
        ]);

        $this->actingAs($this->user, 'api')
             ->putJson("/api/cart/{$cartItem->id}", ['quantity' => CartLimit::PIZZA->value + 1])
             ->assertStatus(400)
             ->assertJson(['message' => 'Cannot have more than ' . CartLimit::PIZZA->value . ' items of category Пицца.']);
    }

    #[Test]
    public function it_deletes_cart_item()
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        $cartItem = CartItem::factory()->create(['cart_id' => $cart->id]);

        $this->actingAs($this->user, 'api')
             ->deleteJson("/api/cart/{$cartItem->id}")
             ->assertStatus(200)
             ->assertJson(['message' => 'Cart item removed']);

        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }
}
