<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Cart;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\OrderService;

class OrderController extends Controller
{
    protected $orderService;
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }
    public function store(OrderRequest $request)
    {
        try {
            $result = $this->orderService->createOrder($request->validated());
            return response()->json([
                'message' => $result['message'],
                'order' => new OrderResource($result['order']),
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], $th->getCode() ?: 500);
        }
    }

    public function index()
    {
        $orders = $this->orderService->getOrders();
        \Log::info('Orders Data: ', $orders->toArray());

        return OrderResource::collection($orders);
    }

    public function show(Order $order)
    {
        try {
            $order = $this->orderService->getOrder($order);
            return new OrderResource($order->loadMissing('location', 'orderItems'));
        } catch (\Throwable $th) {
           return response()->json(['message' => $th->getMessage()], $th->getCode() ?: 500);
        }
    }

    public function destroy(Order $order)
    {
        try {
            $result = $this->orderService->cancelOrder($order);
            return response()->json($result);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], $th->getCode() ?: 500);
        }
    }

    public function update(OrderRequest $request, Order $order)
    {
        try {
            $result = $this->orderService->updateOrder($order, $request->validated());
            return response()->json([
                'message' => $result['message'],
                'order' => new OrderResource($result['order']),
            ]);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], $th->getCode() ?: 500);
        }
    }
}
