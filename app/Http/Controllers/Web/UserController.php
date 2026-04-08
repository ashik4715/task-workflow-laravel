<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('roleModel.permissions')
            ->when($request->role, function ($query) use ($request) {
                $query->where('role', $request->role);
            })->when($request->role_id, function ($query) use ($request) {
                $query->where('role_id', $request->role_id);
            })->when($request->is_active !== null, function ($query) use ($request) {
                $query->where('is_active', $request->boolean('is_active'));
            })->paginate(10);

        $roles = Role::where('is_active', true)->get();

        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::where('is_active', true)->get();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:USER,ADMIN',
            'role_id' => 'nullable|exists:roles,id',
            'is_active' => 'required|boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'role_id' => $validated['role_id'] ?? null,
            'is_active' => $validated['is_active'],
        ]);

        AuditLog::log(User::class, $user->id, 'created', null, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'role_id' => $user->role_id,
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully');
    }

    public function show(User $user)
    {
        $roles = Role::where('is_active', true)->get();
        return view('users.show', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'role' => 'sometimes|in:USER,ADMIN',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        $oldData = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'role_id' => $user->role_id,
        ];

        $user->update($validated);

        $newData = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'role_id' => $user->role_id,
        ];

        AuditLog::log(User::class, $user->id, 'updated', $oldData, $newData);

        return back()->with('success', 'User updated successfully');
    }

    public function updateStatus(Request $request, User $user)
    {
        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $oldData = ['is_active' => $user->is_active];

        $user->update($validated);

        $newData = ['is_active' => $user->is_active];

        AuditLog::log(User::class, $user->id, 'status_changed', $oldData, $newData);

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

        $oldData = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ];
        $user->delete();

        AuditLog::log(User::class, $user->id, 'deleted', $oldData, null);

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
            ->paginate(10);

        return view('users.audit-logs', compact('logs'));
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

        $oldData = ['name' => $user->name, 'email' => $user->email];

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (!empty($validated['password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        $newData = ['name' => $user->name, 'email' => $user->email];

        AuditLog::log(User::class, $user->id, 'profile_updated', $oldData, $newData);

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully.');
    }
}