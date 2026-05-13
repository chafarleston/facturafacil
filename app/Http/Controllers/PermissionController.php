<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::orderBy('module')->orderBy('name')->get();
        $groupedPermissions = $permissions->groupBy('module');
        return view('admin.permissions.index', compact('permissions', 'groupedPermissions'));
    }

    public function create()
    {
        $modules = Permission::getModules();
        return view('admin.permissions.create', compact('modules'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'module' => 'required|string',
            'description' => 'nullable|string|max:500',
            'status' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($request->name, '_');

        $exists = Permission::where('slug', $validated['slug'])->exists();
        if ($exists) {
            return back()->with('error', 'Ya existe un permiso con ese nombre');
        }

        Permission::create($validated);

        return redirect()->route('permissions.index')->with('success', 'Permiso creado');
    }

    public function edit(Permission $permission)
    {
        $modules = Permission::getModules();
        return view('admin.permissions.edit', compact('permission', 'modules'));
    }

    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'module' => 'required|string',
            'description' => 'nullable|string|max:500',
            'status' => 'boolean',
        ]);

        $newSlug = Str::slug($request->name, '_');
        $exists = Permission::where('slug', $newSlug)->where('id', '!=', $permission->id)->exists();
        if ($exists) {
            return back()->with('error', 'Ya existe un permiso con ese nombre');
        }

        $validated['slug'] = $newSlug;
        $permission->update($validated);

        return redirect()->route('permissions.index')->with('success', 'Permiso actualizado');
    }

    public function destroy(Permission $permission)
    {
        $permission->roles()->detach();
        $permission->delete();

        return redirect()->route('permissions.index')->with('success', 'Permiso eliminado');
    }
}