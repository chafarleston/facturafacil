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
        ];

        $company = Company::first();
        if (!$company) {
            $company = Company::create([
                'ruc' => '99999999999',
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
