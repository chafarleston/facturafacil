<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        return view('admin.roles.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'status' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($request->name, '_');
        $validated['is_system'] = false;

        $exists = Role::where('slug', $validated['slug'])->exists();
        if ($exists) {
            return back()->with('error', 'Ya existe un rol con ese nombre');
        }

        Role::create($validated);

        return redirect()->route('roles.index')->with('success', 'Rol creado');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::where('status', true)->orderBy('module')->orderBy('name')->get();
        $groupedPermissions = $permissions->groupBy('module');
        $rolePermissions = $role->permissions()->pluck('id')->toArray();

        return view('admin.roles.edit', compact('role', 'groupedPermissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'status' => 'boolean',
            'permissions' => 'array',
        ]);

        $newSlug = Str::slug($request->name, '_');
        $exists = Role::where('slug', $newSlug)->where('id', '!=', $role->id)->exists();
        if ($exists) {
            return back()->with('error', 'Ya existe un rol con ese nombre');
        }

        $role->update([
            'name' => $validated['name'],
            'slug' => $newSlug,
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] ?? true,
        ]);

        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('roles.index')->with('success', 'Rol actualizado');
    }

    public function destroy(Role $role)
    {
        if ($role->is_system) {
            return back()->with('error', 'No se puede eliminar un rol del sistema');
        }

        $role->users()->detach();
        $role->permissions()->detach();
        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Rol eliminado');
    }
}