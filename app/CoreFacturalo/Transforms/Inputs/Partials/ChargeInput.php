<?php

namespace App\CoreFacturalo\Transforms\Inputs\Partials;

class ChargeInput
{
    public static function transform($inputs)
    {
        $charges = null;
        if(key_exists('cargos', $inputs)) {
            foreach ($inputs['cargos'] as $row)
            {
                $code = $row['codigo'];
                $description = $row['descripcion'];
                $percentage = $row['porcentaje'];
                $amount = $row['monto'];
                $base = $row['base'];

                $charges[] = [
                    'code' => $code,
                    'description' => $description,
                    'percentage' => $percentage,
                    'amount' => $amount,
                    'base' => $base,
                ];
            }
        }

        return $charges;
    }
}