<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function store(OrderRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = auth('api')->user();
            $cart = Cart::where('user_id', $user->id)->with('cartItems.product')->first();

            if (!$cart || $cart->cartItems->isEmpty()) {
                return response()->json(['message' => 'Cart is empty'], 400);
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
                'address' => $request->address,
                'city' => $request->city,
                'country' => $request->country,
                'order_id' => $order->id,
            ]);

            $cart->cartItems()->delete();

            DB::commit();

            return response()->json([
                'message' => 'Order created successfully',
                'order' => new OrderResource($order),
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create order', 'error' => $th->getMessage()], 500);
        }
    }

    public function index()
    {
        $user = auth('api')->user();
        $orders = Order::whereHas('cart', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['location', 'orderItems'])->get();

        \Log::info('Orders Data: ', $orders->toArray());

        return OrderResource::collection($orders);
    }

    public function show(Order $order)
    {
        if ($order->cart->user_id !== auth('api')->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return new OrderResource($order->loadMissing('location', 'orderItems'));
    }

    public function destroy(Order $order)
    {
        if ($order->cart->user_id !== auth('api')->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $order->update(['status' => OrderStatus::CANCELLED]);

        return response()->json(['message' => 'Order cancelled successfully']);
    }

    public function update(OrderRequest $request, Order $order)
    {
        if ($order->cart->user_id !== auth('api')->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $order->location->update([
            'address' => $request->address,
            'city' => $request->city,
            'country' => $request->country,
        ]);

        return response()->json([
            'message' => 'Order updated successfully',
            'order' => new OrderResource($order),
        ]);
    }
}
