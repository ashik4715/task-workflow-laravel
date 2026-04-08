<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::when($request->role, function ($query) use ($request) {
            $query->where('role', $request->role);
        })->when($request->is_active !== null, function ($query) use ($request) {
            $query->where('is_active', $request->boolean('is_active'));
        })->paginate(10);

        return view('users.index', compact('users'));
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'role' => 'sometimes|in:USER,ADMIN',
        ]);

        $oldValues = $user->toArray();
        $user->update($validated);
        
        if (isset($validated['role'])) {
            $user->role = $validated['role'];
            $user->save();
        }
        
        $newValues = $user->fresh()->toArray();

        AuditLog::log(User::class, $user->id, 'updated', $oldValues, $newValues);

        return back()->with('success', 'User updated successfully');
    }

    public function updateStatus(Request $request, User $user)
    {
        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $oldValues = $user->toArray();
        $user->update($validated);
        $newValues = $user->fresh()->toArray();

        AuditLog::log(User::class, $user->id, 'status_changed', $oldValues, $newValues);

        return back()->with('success', 'User status updated successfully');
    }

    public function destroy(User $user)
    {
        $currentUser = Auth::user();
        if ($user->id === $currentUser->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        if ($user->role === 'ADMIN') {
            return back()->with('error', 'Cannot delete ADMIN user.');
        }

        $oldValues = $user->toArray();
        $user->delete();

        AuditLog::log(User::class, $user->id, 'deleted', $oldValues, null);

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function auditLogs(Request $request)
    {
        $logs = AuditLog::with('user')
            ->when($request->entity_type, function ($query) use ($request) {
                $query->where('entity_type', $request->entity_type);
            })
            ->when($request->action, function ($query) use ($request) {
                $query->where('action', $request->action);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('users.audit-logs', compact('logs'));
    }

    public function permissions(User $user)
    {
        $allPermissions = Permission::all();
        $userPermissions = $user->permissions->pluck('id')->toArray();

        return view('users.permissions', compact('user', 'allPermissions', 'userPermissions'));
    }

    public function updatePermissions(Request $request, User $user)
    {
        $validated = $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        $user->permissions()->sync($validated['permissions'] ?? []);

        AuditLog::log(User::class, $user->id, 'permissions_updated', [], ['permissions' => $validated['permissions'] ?? []]);

        return back()->with('success', 'Permissions updated successfully');
    }

    public function editProfile()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'current_password' => 'nullable|required_with:password|string',
            'password' => 'nullable|min:8|confirmed',
        ]);

        // Update name and email
        $oldValues = $user->toArray();
        $user->name = $validated['name'];
        $user->email = $validated['email'];

        // Update password if provided
        if (!empty($validated['password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'current_password' => 'Current password is incorrect.',
                ]);
            }
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        $newValues = $user->fresh()->toArray();

        AuditLog::log(User::class, $user->id, 'profile_updated', $oldValues, $newValues);

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully.');
    }
}
