<?php

namespace App\Http\Controllers;

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

        $order = Order::where('user_id', $request->user()->id)
            ->findOrFail($orderId);

        // Only allow modifications to pending orders
        abort_unless($order->status === 'pending', 403,
            'This order cannot be modified.');

        $orderItem = $order->orderItems()->findOrFail($itemId);

        DB::transaction(function () use ($order, $orderItem, $validated) {

            // Update quantity
            $orderItem->update([
                'quantity' => $validated['quantity']
            ]);

            // Recalculate the order total
            $total = $order->orderItems()
                ->get()
                ->sum(function ($item) {
                    return $item->price * $item->quantity;
                });

            $order->update([
                'total_amount' => $total
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Order item updated successfully',
            'order' => $order->load('orderItems.menuItem')
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
                ->get()
                ->sum(function ($item) {
                    return $item->price * $item->quantity;
                });

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


