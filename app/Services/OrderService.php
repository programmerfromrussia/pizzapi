<?php

namespace App\Services;

use App\DTO\OrderDTO;
use App\Enums\OrderStatus;
use App\Models\Cart;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createOrder(OrderDTO $dto, int $userId): array
    {
        DB::beginTransaction();
        try {
            $cart = Cart::where('user_id', $userId)->with('cartItems.product')->first();

            if (!$cart || $cart->cartItems->isEmpty()) {
                throw new \Exception('Cart is empty');
            }

            $order = Order::create([
                'cart_id' => $cart->id,
                'status' => OrderStatus::PROCESSING,
            ]);

            foreach ($cart->cartItems as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->product->price,
                ]);
            }

            Location::create([
                'address' => $dto->address,
                'city' => $dto->city,
                'country' => $dto->country,
                'order_id' => $order->id,
            ]);

            $cart->cartItems()->delete();

            DB::commit();

            return [
                'message' => 'Order created successfully',
                'order' => $order,
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function getOrders(int $userId): LengthAwarePaginator
    {
        return Order::whereHas('cart', function ($query) use ($userId): void {
            $query->where('user_id', $userId);
        })->with(relations: ['location', 'orderItems'])
        ->paginate();
    }

    public function getOrder(Order $order): Order
    {
        return $order->loadMissing('location', 'orderItems');
    }

    public function cancelOrder(Order $order): array
    {
        $order->update(['status' => OrderStatus::CANCELLED]);

        return ['message' => 'Order cancelled successfully'];
    }
    public function updateOrder(Order $order, OrderDTO $dto): array
    {
        $order->location->update([
            'address' => $dto->address,
            'city' => $dto->city,
            'country' => $dto->country,
        ]);

        return [
            'message' => 'Order updated successfully',
            'order' => $order,
        ];
    }
}
