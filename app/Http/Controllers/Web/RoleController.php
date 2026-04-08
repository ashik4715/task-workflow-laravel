<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::when($request->is_active !== null, function ($query) use ($request) {
            $query->where('is_active', $request->boolean('is_active'));
        })->paginate(10);

        return view('roles.index', compact('roles'));
    }

    public function show(Role $role)
    {
        $allPermissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('roles.show', compact('role', 'allPermissions', 'rolePermissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:roles,name',
            'description' => 'nullable|string|max:255',
        ]);

        Role::create($validated);

        return redirect()->route('roles.index')->with('success', 'Role created successfully');
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:50|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        $role->update($validated);

        return redirect()->route('roles.show', $role->id)->with('success', 'Role updated successfully');
    }

    public function updatePermissions(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);

        AuditLog::log(Role::class, $role->id, 'permissions_updated', [], ['permissions' => $validated['permissions'] ?? []]);

        return back()->with('success', 'Permissions updated successfully');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'ADMIN') {
            return back()->with('error', 'Cannot delete ADMIN role');
        }

        User::where('role_id', $role->id)->update(['role_id' => null]);

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully');
    }
}