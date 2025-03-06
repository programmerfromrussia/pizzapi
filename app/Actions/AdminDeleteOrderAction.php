<?php

namespace App\Actions;

use App\Models\Order;

class AdminDeleteOrderAction
{
    public function execute(int $orderId): void
    {
        $order = Order::findOrFail($orderId);

        $order->delete();
    }
}
