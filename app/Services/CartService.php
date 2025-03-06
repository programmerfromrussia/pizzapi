<?php

namespace App\Services;

use App\Enums\CartLimit;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class CartService
{
    public function getCartId()
    {
        $userId = auth('api')->id();
        $cart = Cart::firstOrCreate(['user_id' => $userId]);
        return $cart->id;
    }

    public function getCart()
    {
        $userId = auth('api')->id();
        return Cart::firstOrCreate(['user_id' => $userId]);
    }

    public function getCartItems()
    {
        $userId = auth('api')->id();
        $cart = Cart::where('user_id', $userId)
                    ->with('cartItems')
                    ->first();

        return $cart ? $cart->cartItems : ['message' => 'No items found -_-'];
    }

    public function addItemToCart(array $validated)
    {
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
                throw new \Exception("Cannot add more than {$categoryLimits[$product->category]} items of category {$product->category}.");
            }
        }

        $cart = $this->getCart();
        $cartItem = $cart->cartItems()->updateOrCreate(
            ['product_id' => $validated['product_id']],
            ['quantity' => $validated['quantity'], 'price' => $product->price]
        );

        return $cartItem;
    }

    public function updateCartItem(int $id, array $validated)
    {
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
                throw new \Exception("Cannot have more than {$categoryLimits[$product->category]} items of category {$product->category}.");
            }
        }

        $cartItem->update(['quantity' => $validated['quantity']]);

        return $cartItem;
    }

    public function removeCartItem(int $id)
    {
        $cartItem = CartItem::whereHas('cart', function ($query) {
            $query->where('user_id', auth('api')->id());
        })->findOrFail($id);

        $cartItem->delete();

        return ['message' => 'Cart item removed'];
    }
}
