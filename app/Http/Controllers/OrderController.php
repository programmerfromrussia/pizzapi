<?php

namespace App\Http\Controllers;

use App\DTO\OrderDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    protected $orderService;
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }
    public function store(OrderRequest $request): JsonResponse
    {
        try {
            $dto = new OrderDTO($request->validated());
            $result = $this->orderService->createOrder($dto);
            return response()->json([
                'message' => $result['message'],
                'order' => new OrderResource($result['order']),
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], $th->getCode() ?: 500);
        }
    }

    public function index(): AnonymousResourceCollection
    {
        $orders = $this->orderService->getOrders();

        return OrderResource::collection($orders);
    }

    public function show(Order $order): JsonResponse|OrderResource
    {
        try {
            $order = $this->orderService->getOrder($order);
            return new OrderResource($order->loadMissing('location', 'orderItems'));
        } catch (\Throwable $th) {
           return response()->json(['message' => $th->getMessage()], $th->getCode() ?: 500);
        }
    }

    public function destroy(Order $order): JsonResponse
    {
        try {
            $result = $this->orderService->cancelOrder($order);
            return response()->json($result);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], $th->getCode() ?: 500);
        }
    }

    public function update(OrderRequest $request, Order $order): JsonResponse
    {
        try {
            $dto = new OrderDTO($request->validated());
            $result = $this->orderService->updateOrder($order, $dto);
            return response()->json([
                'message' => $result['message'],
                'order' => new OrderResource($result['order']),
            ]);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], $th->getCode() ?: 500);
        }
    }
}
