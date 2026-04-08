<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $users = User::when($request->has('role'), function ($query) use ($request) {
            $query->where('role', $request->role);
        })->when($request->has('is_active'), function ($query) use ($request) {
            $query->where('is_active', $request->boolean('is_active'));
        })->paginate($request->get('per_page', 15));

        return response()->json($users);
    }

    public function show(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'is_active' => 'sometimes|boolean',
        ]);

        $oldValues = $user->toArray();
        $user->update($validated);
        $newValues = $user->fresh()->toArray();

        \App\Models\AuditLog::log(User::class, $user->id, 'updated', $oldValues, $newValues);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user,
        ]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $oldValues = $user->toArray();
        $user->update($validated);
        $newValues = $user->fresh()->toArray();

        \App\Models\AuditLog::log(User::class, $user->id, 'status_changed', $oldValues, $newValues);

        return response()->json([
            'message' => 'User status updated successfully',
            'user' => $user,
        ]);
    }

    public function profile(): JsonResponse
    {
        return response()->json(Auth::user());
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8|confirmed',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $oldValues = $user->toArray();
        $user->update($validated);
        $newValues = $user->fresh()->toArray();

        \App\Models\AuditLog::log(User::class, $user->id, 'profile_updated', $oldValues, $newValues);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user,
        ]);
    }
}
