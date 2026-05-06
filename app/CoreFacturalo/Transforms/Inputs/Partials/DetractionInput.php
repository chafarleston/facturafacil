<?php

namespace App\CoreFacturalo\Transforms\Inputs\Partials;

class DetractionInput
{
    public static function transform($inputs)
    {
        if(key_exists('detraccion', $inputs)) {
            $detraction = $inputs['detraccion'];

            $code = $detraction['codigo'];
            $percentage = $detraction['porcentaje'];
            $amount = $detraction['monto'];
            $payment_method_id = $detraction['codigo_metodo_pago'];
            $bank_account = $detraction['cuenta_bancaria'];

            return [
                'code' => $code,
                'percentage' => $percentage,
                'amount' => $amount,
                'payment_method_id' => $payment_method_id,
                'bank_account' => $bank_account,
            ];
        }
        return null;
    }
}