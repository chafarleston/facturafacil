<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        User::where('email', 'superadmin@example.com')->delete();

        $user = User::updateOrCreate(
            ['email' => 'Caja@gmail.com'],
            [
                'name' => 'Cajero',
                'password' => Hash::make('222938'),
                'role' => 'cajero',
            ]
        );

        $role = Role::where('slug', 'cajero')->first();
        if ($role && !$user->roles()->where('role_id', $role->id)->exists()) {
            $user->roles()->attach($role->id);
        }
    }
}
