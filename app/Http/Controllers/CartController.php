<?php
namespace App\Http\Controllers;

use App\Enums\CartLimit;
use App\Http\Controllers\Controller;
use App\Http\Requests\CartRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    protected $cartService;
    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }
    public function index()
    {
        $cartItems = $this->cartService->getCartItems();

        return response()->json($cartItems ? $cartItems : ['message' => 'No items found -_-'], 200);
    }

    public function store(CartRequest $request)
    {
        try {
            $validated = $request->validated();
            $cartItem = $this->cartService->addItemToCart($validated);

            return response()->json([
                'item' => $cartItem,
                'identifier' => auth('api')->id(),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
    public function update(Request $request, int $id)
    {
        try {
            $validated = $request->validate([
                'quantity' => 'required|integer|min:1',
            ]);

            $cartItem = $this->cartService->updateCartItem($id, $validated);
            return response()->json($cartItem, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function destroy(int $id)
    {
        $result = $this->cartService->removeCartItem($id);

        return response()->json($result, 200);    }
}
