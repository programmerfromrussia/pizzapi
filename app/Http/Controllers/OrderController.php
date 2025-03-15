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
            $user = auth(guard: 'api')->user();

            $dto = new OrderDTO(data: $request->validated());

            $order = $this->orderService->createOrder(dto: $dto, userId: $user->id);

            return response()->json(data: [
                'message' => 'Order created successfully',
                'order' => new OrderResource(resource: $order),
            ], status: 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(data: ['message' => $e->getMessage()], status: 400);
        } catch (\Exception $e) {
            \Log::error(message: 'Order creation failed: ' . $e->getMessage(), context: [
                'exception' => $e,
                'user_id' => auth(guard: 'api')->id(),
            ]);

            return response()->json(data: ['message' => 'An unexpected error occurred'], status: 500);
        }
    }

    public function index(): AnonymousResourceCollection
    {
        $user = auth(guard: 'api')->user();
        $orders = $this->orderService->getOrders(userId: $user->id);

        return OrderResource::collection(resource: $orders);
    }

    public function show(Order $order): JsonResponse|OrderResource
    {
        try {
            $order = $this->orderService->getOrder(order: $order);
            return new OrderResource($order->loadMissing('location', 'orderItems'));
        } catch (\Throwable $th) {
            return response()->json(data: ['message' => $th->getMessage()], status: $th->getCode() ?: 500);
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
            $dto = new OrderDTO(data: $request->validated());

            $updatedOrder = $this->orderService->updateOrder(order: $order, dto: $dto);

            return response()->json(data: [
                'message' => 'Order updated successfully',
                'order' => new OrderResource(resource: $updatedOrder),
            ], status: 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(
                data: [
                'message' => $e->getMessage()],
                status: 400
            );
        } catch (\Exception $e) {
            \Log::error(message: 'Order update failed: ' . $e->getMessage(), context: [
                'exception' => $e,
                'order_id' => $order->id,
            ]);
            return response()->json(
                data: [
                'message' => 'An unexpected error occurred'],
                status: 500
            );
        }
    }
}
