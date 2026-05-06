<?php

namespace App\CoreFacturalo\Transforms\Inputs\Partials;

class ItemAttributeInput
{
    public static function transform($inputs)
    {
        $attributes = null;
        if(key_exists('datos_adicionales', $inputs)) {
            foreach ($inputs['datos_adicionales'] as $row)
            {
                $code = $row['codigo'];
                $name = $row['nombre'];
                $value = array_key_exists('valor', $row)?$row['valor']:null;
                $start_date = array_key_exists('fecha_inicio', $row)?$row['fecha_inicio']:null;
                $end_date = array_key_exists('fecha_fin', $row)?$row['fecha_fin']:null;
                $duration = array_key_exists('duracion', $row)?$row['duracion']:null;

                $attributes[] = [
                    'code' => $code,
                    'name' => $name,
                    'value' => $value,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'duration' => $duration,
                ];
            }
        }

        return $attributes;
    }
}