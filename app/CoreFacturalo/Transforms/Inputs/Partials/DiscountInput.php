<?php

namespace App\CoreFacturalo\Transforms\Inputs\Partials;

class DiscountInput
{
    public static function transform($inputs)
    {
        $discounts = null;
        if(key_exists('descuentos', $inputs)) {
            foreach ($inputs['descuentos'] as $row) {
                $code = $row['codigo'];
                $description = $row['descripcion'];
                $percentage = $row['porcentaje'];
                $amount = $row['monto'];
                $base = $row['base'];

                $discounts[] = [
                    'code' => $code,
                    'description' => $description,
                    'percentage' => $percentage,
                    'amount' => $amount,
                    'base' => $base,
                ];
            }
        }

        return $discounts;
    }
}