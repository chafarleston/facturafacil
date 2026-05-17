<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if (!$company) {
            $company = Company::firstOrCreate([
                'ruc' => '99999999999',
            ], [
                'razon_social' => 'Demo Company',
                'nombre_comercial' => 'Demo Company',
                'direccion' => 'Lima',
                'estado' => true,
            ]);
        }

        Customer::firstOrCreate(
            ['documento_numero' => '88888888'],
            [
                'company_id' => $company->id,
                'documento_tipo' => '1',
                'nombre' => 'Clientes Varios',
                'direccion' => 'Lima',
                'estado' => 'ACTIVO',
            ]
        );

        $this->command->info('✓ Cliente "Clientes Varios" (DNI: 88888888) creado');
    }
}
