<?php

namespace App\Services;

use App\Enums\CartLimit;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Exceptions\CartLimitException;

class CartService
{
    public function getCartId(int $userId): mixed
    {
        $cart = Cart::firstOrCreate(['user_id' => $userId]);
        return $cart->id;
    }

    public function getCart(int $userId): Cart
    {
        return Cart::firstOrCreate(['user_id' => $userId]);
    }

    public function getCartItems(int $userId): mixed
    {
        $cart = Cart::where('user_id', $userId)
                    ->with('cartItems')
                    ->first();

        return $cart ? $cart->cartItems : ['message' => 'No items found -_-'];
    }

    public function addItemToCart(array $validated, int $userId): CartItem
    {
        $product = Product::findOrFail($validated['product_id']);
        if (!$product instanceof Product) {
            throw new \RuntimeException('Expected a Product instance, but got something else.');
        }
        $this->validateCategoryLimit($userId, $product, $validated['quantity']);

        $cart = $this->getCart($userId);
        $cartItem = $cart->cartItems()->updateOrCreate(
            ['product_id' => $validated['product_id']],
            ['quantity' => $validated['quantity'], 'price' => $product->price]
        );

        return $cartItem;
    }

    public function updateCartItem(int $cartId, array $validated, int $userId): CartItem
    {
        $cartItem = CartItem::findOrFail($cartId);
        $product = $cartItem->product;

        $quantityChange = $validated['quantity'] - $cartItem->quantity;
        $this->validateCategoryLimit($userId, $product, $quantityChange);

        $cartItem->update(['quantity' => $validated['quantity']]);
        return $cartItem;
    }

    public function removeCartItem(int $cartId, int $userId): array
    {
        $cartItem = CartItem::whereHas('cart', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->findOrFail($cartId);

        $cartItem->delete();

        return ['message' => 'Cart item removed'];
    }
    private function validateCategoryLimit(int $userId, Product $product, int $quantityChange): void
    {
        $categoryLimits = [
            'Пицца' => CartLimit::PIZZA->value,
            'Напитки' => CartLimit::DRINKS->value,
        ];

        if (!isset($categoryLimits[$product->category])) {
            throw new CartLimitException("Category {$product->category} is not supported.");
        }

        $currentQuantity = CartItem::where('cart_id', $this->getCart(userId: $userId)->id)
                                   ->whereHas('product', function ($query) use ($product): void {
                                       $query->where('category', $product->category);
                                   })
                                   ->sum('quantity');

        $totalQuantity = $currentQuantity + $quantityChange;

        if ($totalQuantity > $categoryLimits[$product->category]) {
            throw new CartLimitException("Cannot have more than {$categoryLimits[$product->category]} items of category {$product->category}.");
        }
    }
}
