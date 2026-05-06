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
            ['serie' => 'F001', 'description' => 'FACTURA'],
            ['serie' => 'B001', 'description' => 'BOLETA'],
            ['serie' => 'FC01', 'description' => 'NOTA DE CRÉDITO FACTURA'],
            ['serie' => 'BC01', 'description' => 'NOTA DE CRÉDITO BOLETA'],
            ['serie' => 'FD01', 'description' => 'NOTA DE DÉBITO FACTURA'],
            ['serie' => 'BD01', 'description' => 'NOTA DE DÉBITO BOLETA'],
            ['serie' => 'R001', 'description' => 'COMPROBANTE DE RETENCIÓN ELECTRÓNICA'],
            ['serie' => 'T001', 'description' => 'GUIA DE REMISIÓN REMITENTE'],
            ['serie' => 'P001', 'description' => 'COMPROBANTE DE PERCEPCIÓN ELECTRÓNICA'],
            ['serie' => 'NV01', 'description' => 'NOTA DE VENTA'],
        ];

        // Ensure there is a company to associate with series
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
            $serieCode = $e['serie'];
            // Determine document type by series code
            if ($serieCode === 'NV01') {
                $tipoDocumento = 'NV';
            } elseif (in_array($serieCode, ['F001'])) {
                $tipoDocumento = '01';
            } elseif (in_array($serieCode, ['B001'])) {
                $tipoDocumento = '03';
            } elseif (in_array($serieCode, ['FC01', 'FD01'])) {
                $tipoDocumento = '01';
            } elseif (in_array($serieCode, ['BC01', 'BD01'])) {
                $tipoDocumento = '03';
            } else {
                $tipoDocumento = '01';
            }
            Serie::updateOrCreate(
                ['serie' => $e['serie']],
                [
                    'serie' => $e['serie'],
                    'tipo_documento' => $tipoDocumento,
                    'numero_actual' => 0,
                    'estado' => 1,
                    'company_id' => $company->id,
                ]
            );
        }
    }
}
