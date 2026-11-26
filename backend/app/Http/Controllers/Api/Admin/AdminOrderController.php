<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminOrderController extends Controller
{
    /** Search and filter all orders, newest first. */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'preparing', 'out_for_delivery', 'delivered', 'cancelled'])],
            'search' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $orders = Order::with('user:id,name,email')
            ->withCount('orderItems')
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('id', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(15);

        return response()->json($orders);
    }

    /** Show one order with its customer, item snapshots, and payments. */
    public function show(Order $order)
    {
        return response()->json([
            'order' => $order->load([
                'user:id,name,email,phone_number',
                'orderItems.menuItem',
                'payments',
            ]),
        ]);
    }

    /** Change the fulfillment status from the admin portal. */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'preparing', 'out_for_delivery', 'delivered', 'cancelled'])],
        ]);

        $order->update(['status' => $validated['status']]);

        return response()->json([
            'message' => 'Order status updated.',
            'order' => $order->fresh()->load('user:id,name,email'),
        ]);
    }
}
