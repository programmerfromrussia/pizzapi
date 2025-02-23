<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderUpdateRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class AdminOrderController extends Controller
{
    /**
     * Update the specified resource in storage.
     */
    public function update(OrderUpdateRequest $request, string $id)
    {
        $validated = $request->validated();

        $order = Order::findOrFail($id);

        if (isset($validated['status'])) {
            $order->status = $validated['status'];
        }

        $locationData = Arr::only($validated, ['address', 'city', 'country']);

        if (!empty($locationData)) {
            $location = $order->location ?? $order->location()->create([]);
            $location->update($locationData);
        }

        $order->save();

        return response()->json([
            'message' => 'Order updated successfully',
            'order' => $order,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $order = Order::findOrFail($id);

        $order->delete();

        return response()->json([
            'message' => 'Order deleted successfully',
        ], 200);
    }
}
