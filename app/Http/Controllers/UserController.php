<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with('roles')->get();
        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        $roles = Role::where('status', true)->get();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'min:8', 'confirmed'],
            'role' => ['required', 'in:admin,user,mozo'],
            'roles' => ['array'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        if (!empty($validated['roles'])) {
            $user->roles()->sync($validated['roles']);
        }

        return redirect()->route('users.index')->with('status', 'Usuario creado');
    }

    public function edit(User $user): View
    {
        $roles = Role::where('status', true)->get();
        $userRoles = $user->roles()->pluck('id')->toArray();
        return view('admin.users.edit', compact('user', 'roles', 'userRoles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 
                \Illuminate\Validation\Rule::unique('users')->ignore($user->id)],
            'role' => ['required', 'in:admin,user,mozo'],
            'roles' => ['array'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        $user->roles()->sync($validated['roles'] ?? []);

        return redirect()->route('users.index')->with('status', 'Usuario actualizado');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->roles()->detach();
        $user->delete();
        return redirect()->route('users.index')->with('status', 'Usuario eliminado');
    }
}