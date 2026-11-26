<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    /**
     * Display all menu items.
     */
    public function index()
    {
        // Customers should only see dishes currently available to order.
        $menuItems = MenuItem::with('category')->where('is_available', true)->latest()->get();

        return response()->json([
            'message' => 'Menu items retrieved successfully',
            'data' => $menuItems
        ], 200);
    }

    /** Administrators need to see both available and hidden menu items. */
    public function adminIndex()
    {
        return response()->json([
            'message' => 'Menu items retrieved successfully',
            'data' => MenuItem::with('category')->latest()->get(),
        ]);
    }

    /**
     * Store a newly created menu item.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'price' => 'required|integer|min:0',
            'image' => 'nullable|string|max:2048',
            'is_available' => 'sometimes|boolean',
        ]);

        $menuItem = MenuItem::create($validated);

        return response()->json([
            'message' => 'Menu item created successfully',
            'data' => $menuItem->load('category')
        ], 201);
    }

    /**
     * Display a specific menu item.
     */
    public function show(string $id)
    {
        $menuItem = MenuItem::with('category')->findOrFail($id);

        return response()->json([
            'message' => 'Menu item retrieved successfully',
            'data' => $menuItem
        ], 200);
    }

    /**
     * Update an existing menu item.
     */
    public function update(Request $request, string $id)
    {
        $menuItem = MenuItem::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'sometimes|required|exists:categories,id',
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|integer|min:0',
            'image' => 'sometimes|nullable|string|max:2048',
            'is_available' => 'sometimes|boolean',
        ]);

        $menuItem->update($validated);

        return response()->json([
            'message' => 'Menu item updated successfully',
            'data' => $menuItem->load('category')
        ], 200);
    }

    /**
     * Remove a menu item.
     */
    public function destroy(string $id)
    {
        $menuItem = MenuItem::findOrFail($id);

        // Order lines preserve the original menu item reference for receipts.
        if ($menuItem->orderItems()->exists()) {
            return response()->json([
                'message' => 'This item is already part of an order and cannot be deleted.',
            ], 409);
        }

        $menuItem->delete();

        return response()->json([
            'message' => 'Menu item deleted successfully'
        ], 200);
    }
}
