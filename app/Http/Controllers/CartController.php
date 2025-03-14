<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartRequest;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected $cartService;
    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }
    public function index(): JsonResponse
    {
        $userId = auth('api')->id();
        $cartItems = $this->cartService->getCartItems($userId);

        return response()->json($cartItems ? $cartItems : ['message' => 'No items found -_-'], 200);
    }

    public function store(CartRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $userId = auth('api')->id();
            $cartItem = $this->cartService->addItemToCart($validated, $userId);

            return response()->json([
                'item' => $cartItem,
                'identifier' => auth('api')->id(),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
    public function update(Request $request, int $cartId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'quantity' => 'required|integer|min:1',
            ]);

            $userId = auth('api')->id();
            $cartItem = $this->cartService->updateCartItem($cartId, $validated, $userId);
            return response()->json($cartItem, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function destroy(int $cartId): JsonResponse
    {

        $userId = auth('api')->id();
        $result = $this->cartService->removeCartItem($cartId, $userId);

        return response()->json($result, 200);
    }
}
