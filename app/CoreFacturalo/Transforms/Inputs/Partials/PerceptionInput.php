<?php

namespace App\CoreFacturalo\Transforms\Inputs\Partials;

class PerceptionInput
{
    public static function transform($inputs)
    {
        if(key_exists('percepcion', $inputs)) {
            $perception = $inputs['percepcion'];

            $code = $perception['codigo'];
            $percentage = $perception['porcentaje'];
            $amount = $perception['monto'];
            $base = $perception['base'];

            return [
                'code' => $code,
                'percentage' => $percentage,
                'amount' => $amount,
                'base' => $base,
            ];
        }
        return null;
    }
}