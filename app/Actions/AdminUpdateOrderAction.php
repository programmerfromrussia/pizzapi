<?php

namespace App\Actions;

use App\Models\Order;
use Illuminate\Support\Arr;

class AdminUpdateOrderAction
{
    public function execute(array $validatedData, int $orderId): Order
    {
        $order = Order::findOrFail($orderId);

        if (isset($validatedData['status'])) {
            $order->status = $validatedData['status'];
        }

        $locationData = Arr::only($validatedData, ['address', 'city', 'country']);
        if (!empty($locationData)) {
            $location = $order->location ?? $order->location()->create(['order_id' => $order->id]);
            $location->update($locationData);
        }

        $order->save();

        return $order;
    }
}
