<?php
namespace App\Http\Controllers;

use App\Enums\CartLimit;
use App\Http\Controllers\Controller;
use App\Http\Requests\CartRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $userId = auth('api')->id();
        $cart = Cart::where('user_id', $userId)
                    ->with('cartItems')
                    ->first();

        return response()->json($cart ? $cart->cartItems : ['message' => 'No items found -_-'], 200);
    }

    public function store(CartRequest $request)
    {
        $validated = $request->validated();
        $product = Product::findOrFail($validated['product_id']);

        $categoryLimits = [
            'Пицца' => CartLimit::PIZZA->value,
            'Напитки' => CartLimit::DRINKS->value,
        ];

        if (isset($categoryLimits[$product->category])) {
            $count = CartItem::where('cart_id', $this->getCartId())
                             ->whereHas('product', function ($query) use ($product) {
                                 $query->where('category', $product->category);
                             })
                             ->sum('quantity');

            if ($count + $validated['quantity'] > $categoryLimits[$product->category]) {
                return response()->json(['message' => "Cannot add more than {$categoryLimits[$product->category]} items of category {$product->category}."], 400);
            }
        }

        $cart = $this->getCart();
        $cartItem = $cart->cartItems()->updateOrCreate(
            ['product_id' => $validated['product_id']],
            ['quantity' => $validated['quantity'], 'price' => $product->price]
        );

        return response()->json([
            'item' => $cartItem,
            'identifier' => auth('api')->id(),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = CartItem::findOrFail($id);
        $product = $cartItem->product;

        $categoryLimits = [
            'Пицца' => CartLimit::PIZZA->value,
            'Напитки' => CartLimit::DRINKS->value,
        ];

        if (isset($categoryLimits[$product->category])) {
            $currentQuantity = $cartItem->quantity;
            $newQuantity = $validated['quantity'];
            $totalQuantity = CartItem::where('cart_id', $this->getCartId())
                                     ->whereHas('product', function ($query) use ($product) {
                                         $query->where('category', $product->category);
                                     })
                                     ->sum('quantity') - $currentQuantity + $newQuantity;

            if ($totalQuantity > $categoryLimits[$product->category]) {
                return response()->json(['message' => "Cannot have more than {$categoryLimits[$product->category]} items of category {$product->category}."], 400);
            }
        }

        $cartItem->update(['quantity' => $validated['quantity']]);

        return response()->json($cartItem, 200);
    }

    public function destroy($id)
    {
        $cartItem = CartItem::whereHas('cart', function ($query) {
            $query->where('user_id', auth('api')->id());
        })->findOrFail($id);

        $cartItem->delete();

        return response()->json(['message' => 'Cart item removed'], 200);
    }

    protected function getCartId()
    {
        $userId = auth('api')->id();
        $cart = Cart::firstOrCreate(['user_id' => $userId]);
        return $cart->id;
    }

    protected function getCart()
    {
        $userId = auth('api')->id();
        return Cart::firstOrCreate(['user_id' => $userId]);
    }
}
