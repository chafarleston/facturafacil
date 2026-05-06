<?php

namespace App\CoreFacturalo\Transforms\Inputs\Partials;

class OptionalInput
{
    public static function transform($inputs)
    {
        if(key_exists('extras', $inputs)) {
            $optional = $inputs['extras'];

            $observations = array_key_exists('observaciones', $optional)?$optional['observaciones']:null;
            $method_payment = array_key_exists('forma_de_pago', $optional)?$optional['forma_de_pago']:null;
            $salesman = array_key_exists('vendedor', $optional)?$optional['vendedor']:null;
            $box_number = array_key_exists('caja', $optional)?$optional['caja']:null;
            $format_pdf = array_key_exists('formato_pdf', $optional)?$optional['formato_pdf']:null;

            return [
                'observations' => $observations,
                'method_payment' => $method_payment,
                'salesman' => $salesman,
                'box_number' => $box_number,
                'format_pdf' => $format_pdf
            ];
        }
        return null;
    }
}