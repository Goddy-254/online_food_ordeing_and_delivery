<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    /** Return the current store overview and recent activity for administrators. */
    public function index()
    {
        $today = now()->toDateString();

        return response()->json([
            'summary' => [
                'orders_today' => Order::whereDate('created_at', $today)->count(),
                'sales_today' => Order::whereDate('created_at', $today)
                    ->where('status', '!=', 'cancelled')
                    ->sum('total_amount'),
                'pending_orders' => Order::where('status', 'pending')->count(),
                'customers' => User::where('role', 'customer')->count(),
                'menu_items' => MenuItem::count(),
            ],
            'sales_last_7_days' => Order::query()
                ->selectRaw('DATE(created_at) as day, SUM(total_amount) as total')
                ->where('created_at', '>=', now()->subDays(6)->startOfDay())
                ->where('status', '!=', 'cancelled')
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('day')
                ->get(),
            'recent_orders' => Order::with('user:id,name,email')
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }
}
