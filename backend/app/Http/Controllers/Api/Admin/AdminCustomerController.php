<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminCustomerController extends Controller
{
    /** List customer accounts with order counts and lifetime spend. */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $customers = User::where('role', 'customer')
            ->withCount('orders')
            ->withSum('orders', 'total_amount')
            ->when($filters['search'] ?? null, fn ($query, $search) => $query
                ->where(fn ($query) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")))
            ->latest()
            ->paginate(15);

        return response()->json($customers);
    }

    /** Show one customer's recent order history. */
    public function show(Request $request, User $user)
    {
        abort_unless($user->role === 'customer', 404);

        return response()->json([
            'customer' => $user->loadCount('orders')->loadSum('orders', 'total_amount'),
            'orders' => $user->orders()->with('orderItems.menuItem')->latest()->paginate(10),
        ]);
    }
}
