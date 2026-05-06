<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    public function run()
    {
        // Demo normal user
        // Ensure a company exists to relate to (reuses the first company if already created)
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
        User::updateOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password'),
                'role' => 'user',
                'company_id' => $company->id,
            ]
        );

        // Demo admin user
        User::updateOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Manager Admin',
                'password' => Hash::make('adminpass'),
                'role' => 'admin',
            ]
        );
    }
}
