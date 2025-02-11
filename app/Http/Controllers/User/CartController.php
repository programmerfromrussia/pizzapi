<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function index()
    {
        $cart = Cart::where('user_id', Auth::id())->orWhere('session_id', session()->getId())->with('cartItems')->first();
        return response()->json($cart ? $cart->cartItems : ['No items found -_-'], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric'
        ]);

        $cart = Auth::check() ? Cart::firstOrCreate(['user_id' => Auth::id()]) :
                               Cart::firstOrCreate(['session_id' => session()->getId()]);

        $cartItem = $cart->cartItems()->updateOrCreate(
            ['product_id' => $validated['product_id']],
            ['quantity' => $validated['quantity'], 'price' => $validated['price']]
        );

        return response()->json(['item' => $cartItem, 'identifier' => Auth::check() ? Auth::id() : session()->getId()], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cartItem = CartItem::findOrFail($id);
        $cartItem->update(['quantity' => $validated['quantity']]);

        return response()->json($cartItem, 200);
    }

    public function destroy($id)
    {
        $cartItem = CartItem::findOrFail($id);
        $cartItem->delete();

        return response()->json(['message' => 'Cart item removed'], 200);
    }
}
