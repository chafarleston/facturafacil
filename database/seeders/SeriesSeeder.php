<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Serie;
use App\Models\Company;

class SeriesSeeder extends Seeder
{
    public function run()
    {
        $entries = [
            ['serie' => 'F001', 'tipo_documento' => '01'],
            ['serie' => 'B001', 'tipo_documento' => '03'],
            ['serie' => 'NV01', 'tipo_documento' => 'NV'],
            ['serie' => 'FC01', 'tipo_documento' => '07'],
            ['serie' => 'BC01', 'tipo_documento' => '07'],
            ['serie' => 'FD01', 'tipo_documento' => '08'],
            ['serie' => 'BD01', 'tipo_documento' => '08'],
        ];

        $company = Company::first();
        if (!$company) {
            $company = Company::create([
                'ruc' => '99999999999',
                'razon_social' => 'Realcomputer SAC',
                'nombre_comercial' => 'Realcomputer SAC',
                'direccion' => 'Calle Sucre #589 Sullana',
                'departamento' => 'Piura',
                'provincia' => 'Sullana',
                'distrito' => 'Sullana',
                'ubigeo' => '200601',
                'telefono' => '927530091',
                'email' => 'rcharles84@gmail.com',
                'estado' => 1,
                'soap_type_id' => 1,
            ]);
        }

        foreach ($entries as $e) {
            Serie::updateOrCreate(
                ['serie' => $e['serie'], 'company_id' => $company->id],
                [
                    'serie' => $e['serie'],
                    'tipo_documento' => $e['tipo_documento'],
                    'numero_actual' => 0,
                    'estado' => 'ACTIVO',
                    'company_id' => $company->id,
                ]
            );
        }
    }
}
