<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    protected function syncRole(User $user, string $roleSlug): void
    {
        $role = Role::where('slug', $roleSlug)->first();
        if ($role && !$user->roles()->where('role_id', $role->id)->exists()) {
            $user->roles()->attach($role->id);
        }
    }

    public function run()
    {
        // Demo normal user
        $company = \App\Models\Company::first();
        if (!$company) {
            $company = \App\Models\Company::firstOrCreate([
                'ruc' => '99999999999'
            ], [
                'razon_social' => 'Demo Company',
                'nombre_comercial' => 'Demo Company',
                'direccion' => 'Lima',
                'departamento' => 'Lima',
                'provincia' => 'Lima',
                'distrito' => 'LIMA',
                'ubigeo' => '150101',
                'telefono' => '999999999',
                'email' => 'demo@local',
                'estado' => 1,
                'soap_type_id' => 1,
            ]);
        }

        $user = User::updateOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password'),
                'role' => 'user',
                'company_id' => $company->id,
            ]
        );
        $this->syncRole($user, 'user');

        // Demo admin user
        $admin = User::updateOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Manager Admin',
                'password' => Hash::make('adminpass'),
                'role' => 'admin',
                'company_id' => $company->id,
            ]
        );
        $this->syncRole($admin, 'admin');

        // Demo mozo user
        $mozo = User::updateOrCreate(
            ['email' => 'mozo@gmail.com'],
            [
                'name' => 'Mozo Restaurante',
                'password' => Hash::make('mozo123'),
                'role' => 'mozo',
                'company_id' => $company->id,
            ]
        );
        $this->syncRole($mozo, 'mozo');
    }
}
