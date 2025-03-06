<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Cart;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function createOrder(array $data)
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
                'address' => $data['address'],
                'city' => $data['city'],
                'country' => $data['country'],
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
            Log::error('Failed to create order: ' . $th->getMessage());
            throw $th;
        }
    }

    public function getOrders()
    {
        $user = auth('api')->user();
        return Order::whereHas('cart', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['location', 'orderItems'])->get();
    }

    public function getOrder(Order $order)
    {
        if ($order->cart->user_id !== auth('api')->id()) {
            throw new \Exception('Unauthorized user, авторизуйся, ок?', 403);
        }

        return $order->loadMissing('location', 'orderItems');
    }

    public function cancelOrder(Order $order)
    {
        if ($order->cart->user_id !== auth('api')->id()) {
            throw new \Exception('Unauthorized', 403);
        }

        $order->update(['status' => OrderStatus::CANCELLED]);

        return ['message' => 'Order cancelled successfully'];
    }

    public function updateOrder(Order $order, array $data)
    {
        if ($order->cart->user_id !== auth('api')->id()) {
            throw new \Exception('Unauthorized', 403);
        }

        $order->location->update([
            'address' => $data['address'],
            'city' => $data['city'],
            'country' => $data['country'],
        ]);

        return [
            'message' => 'Order updated successfully',
            'order' => $order,
        ];
    }
}
