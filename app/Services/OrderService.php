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
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function createOrder(OrderDTO $dto): array
    {
        DB::beginTransaction();
        try {
            $user = auth('api')->user();
            $cart = Cart::where('user_id', $user->id)->with('cartItems.product')->first();

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

    public function getOrders(): LengthAwarePaginator
    {
        $user = auth('api')->user();
        return Order::whereHas('cart', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['location', 'orderItems'])
        ->paginate();
    }

    public function getOrder(Order $order): Order
    {
        if ($order->cart->user_id !== auth('api')->id()) {
            throw new \Exception('Unauthorized user, авторизуйся, ок?', 403);
        }

        return $order->loadMissing('location', 'orderItems');
    }

    public function cancelOrder(Order $order): array
    {
        if ($order->cart->user_id !== auth('api')->id()) {
            throw new \Exception('Unauthorized', 403);
        }

        $order->update(['status' => OrderStatus::CANCELLED]);

        return ['message' => 'Order cancelled successfully'];
    }
    public function updateOrder(Order $order, OrderDTO $dto): array
    {
        if ($order->cart->user_id !== auth('api')->id()) {
            throw new \Exception('Unauthorized', 403);
        }

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
