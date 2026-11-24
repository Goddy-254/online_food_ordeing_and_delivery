<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
   
    // Get all orders belonging to the logged-in customer
    public function index(Request $request)
    {
        $orders = Order::with('orderItems.menuItem')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'orders' => $orders
        ], 200);
    }


    // Create a new order
    public function store(Request $request)
    {
        $validated = $request->validate([
            'delivery_address' => 'required|string|max:255',

            'items' => 'required|array|min:1',

            'items.*.menu_item_id' => 'required|exists:menu_items,id',

            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $order = DB::transaction(function () use ($validated, $request) {

            // Create the order first
            $order = Order::create([
                'user_id' => $request->user()->id,
                'delivery_address' => $validated['delivery_address'],
                'total_amount' => 0,
                'status' => 'pending',
            ]);

            $totalAmount = 0;

            // Loop through the ordered items
            foreach ($validated['items'] as $item) {

                $menuItem = MenuItem::findOrFail(
                    $item['menu_item_id']
                );

                $quantity = $item['quantity'];

                // Calculate item subtotal
                $subtotal = $menuItem->price * $quantity;

                // Save the order item
                $order->orderItems()->create([
                    'menu_item_id' => $menuItem->id,
                    'quantity' => $quantity,
                    'price' => $menuItem->price,
                ]);

                // Add subtotal to total
                $totalAmount += $subtotal;
            }

            // Update the order with the calculated total
            $order->update([
                'total_amount' => $totalAmount
            ]);

            return $order;
        });

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully',
            'order' => $order->load('orderItems.menuItem')
        ], 201);
    }


    // Get one order
    public function show(Request $request, string $id)
    {
        $order = Order::with('orderItems.menuItem')
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'order' => $order
        ], 200);
    }
}
 

