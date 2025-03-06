<?php

namespace App\Http\Controllers;

use App\Actions\AdminDeleteOrderAction;
use App\Actions\AdminUpdateOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderUpdateRequest;
use Illuminate\Support\Facades\App;

class AdminOrderController extends Controller
{
    // protected $updateOrderAction;
    // public function __construct(AdminUpdateOrderAction $updateOrderAction)
    // {
    //     $this->updateOrderAction = $updateOrderAction;
    // }
    public function update(OrderUpdateRequest $request, int $id)
    {
        $updateOrderAction = App::make(AdminUpdateOrderAction::class);

        $validated = $request->validated();

        $order = $updateOrderAction->execute($validated, $id);

        return response()->json([
            'message' => 'Order updated successfully',
            'order' => $order,
        ], 200);
    }
    public function destroy(int $id)
    {
        $deleteOrderAction = App::make(AdminDeleteOrderAction::class);

        $order = $deleteOrderAction->execute($id);

        return response()->json([
            'message' => 'Order deleted successfully',
        ], 200);
    }
}
