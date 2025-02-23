<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cart_id' => Cart::factory(),
            'status' => $this->faker->randomElement([
                OrderStatus::PROCESSING,
                OrderStatus::DELIVERED,
                OrderStatus::CANCELLED,
            ]),
        ];
    }
}
