<?php

namespace App\CoreFacturalo\Transforms\Inputs\Partials;

use App\CoreFacturalo\Helpers\Number\NumberLetter;

class LegendInput
{
    public static function transform($inputs)
    {
        $legends = [];
        if(key_exists('leyendas', $inputs)) {
            foreach ($inputs['leyendas'] as $row)
            {
                $code = $row['codigo'];
                $value = $row['valor'];
                $legends[] = [
                    'code' => $code,
                    'value' => $value,
                ];
            }
        }

        $legends[] = [
            'code' => 1000,
            'value' => NumberLetter::convertToLetter($inputs['totales']['total_venta'])
        ];

        return $legends;
    }
}