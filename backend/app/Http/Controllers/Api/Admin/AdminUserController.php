<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    /** List accounts and roles so an administrator can review access. */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        return response()->json(User::query()
            ->select(['id', 'name', 'email', 'role', 'created_at'])
            ->when($filters['search'] ?? null, fn ($query, $search) => $query
                ->where(fn ($query) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")))
            ->latest()
            ->paginate(15));
    }

    /** Change a user's role while protecting the last administrator account. */
    public function updateRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(['admin', 'customer'])],
        ]);

        if ($user->is($request->user()) && $validated['role'] !== 'admin') {
            return response()->json(['message' => 'You cannot remove your own administrator access.'], 422);
        }

        if ($user->role === 'admin' && $validated['role'] !== 'admin'
            && User::where('role', 'admin')->count() <= 1) {
            return response()->json(['message' => 'At least one administrator account must remain.'], 422);
        }

        $user->update(['role' => $validated['role']]);

        return response()->json([
            'message' => 'User role updated.',
            'user' => $user->only(['id', 'name', 'email', 'role']),
        ]);
    }
}
