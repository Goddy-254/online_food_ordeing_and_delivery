<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderItemController extends Controller
{
    //
    // Update the quantity of an order item
  
public function update(Request $request, string $orderId, string $itemId)
{
    $validated = $request->validate([
        'quantity' => 'required|integer|min:1',
    ]);

    // Find the order belonging to the authenticated customer
    $order = Order::where('user_id', $request->user()->id)
        ->findOrFail($orderId);

    // Only pending orders can be modified
    abort_unless(
        $order->status === 'pending',
        403,
        'This order cannot be modified.'
    );

    // Find the item belonging to this particular order
    $orderItem = $order->orderItems()->findOrFail($itemId);

    DB::transaction(function () use ($order, $orderItem, $validated) {

        // Update the quantity
        $orderItem->update([
            'quantity' => $validated['quantity']
        ]);

        // Retrieve all items belonging to this order
        $orderItems = $order->orderItems()->get();

        // Calculate the new total
        $total = $orderItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        // Save the new total in the orders table
        $order->update([
            'total_amount' => $total
        ]);
    });

    // Return the updated order
    return response()->json([
        'success' => true,
        'message' => 'Order item updated successfully',
        'order' => $order->fresh()->load('orderItems.menuItem')
    ], 200);
}



    // Remove an item from an order
    public function destroy(Request $request, string $orderId, string $itemId)
    {
        $order = Order::where('user_id', $request->user()->id)
            ->findOrFail($orderId);

        abort_unless($order->status === 'pending', 403,
            'This order cannot be modified.');

        $orderItem = $order->orderItems()->findOrFail($itemId);

        DB::transaction(function () use ($order, $orderItem) {

            // Delete the order item
            $orderItem->delete();

            // Recalculate the total
            $total = $order->orderItems()
                ->selectRaw('COALESCE(SUM(price * quantity), 0) as total')
                ->value('total');

            $order->update([
                'total_amount' => $total
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Order item removed successfully',
            'order' => $order->load('orderItems.menuItem')
        ], 200);
    }
}


