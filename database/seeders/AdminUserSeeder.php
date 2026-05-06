<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $adminEmail = env('ADMIN_EMAIL', 'admin@example.com');
        $adminPassword = env('ADMIN_PASSWORD', 'secret123');
        $adminName = env('ADMIN_NAME', 'Administrador');

        // Ensure a company exists and assign to admin
        $company = Company::firstOrCreate(
            ['ruc' => '99999999999'],
            [
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
            ]
        );

        User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => $adminName,
                'password' => Hash::make($adminPassword),
                'role' => 'admin',
                'company_id' => $company->id,
            ]
        );

        // Ensure a non-admin demo user exists
        User::updateOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password'),
                'role' => 'user',
                'company_id' => $company->id,
            ]
        );

        // Adicional: crear/asegurar otro administrador específico
        User::updateOrCreate(
            ['email' => 'rcharles84@gmail.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('Noor4322@'),
                'role' => 'admin',
                'company_id' => $company->id,
            ]
        );
    }
}
