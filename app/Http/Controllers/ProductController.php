<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::all();

        return response()->json($products, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $product = Product::findOrFail($id);
        if ($product) {
            return response()->json($product, 200);
        }
        return response()->json(['message' => 'Product not found'], 404);

    }
}
