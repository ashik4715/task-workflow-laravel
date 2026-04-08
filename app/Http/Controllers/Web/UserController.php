<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuditLog;
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
        ]);

        $oldValues = $user->toArray();
        $user->update($validated);
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
}
