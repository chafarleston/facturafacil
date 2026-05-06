<?php

namespace App\CoreFacturalo\Transforms\Inputs\Partials;

class PrepaymentInput
{
    public static function transform($inputs)
    {
        $prepayments = null;
        if(key_exists('anticipos', $inputs)) {
            foreach ($inputs['anticipos'] as $row)
            {
                $number = $row['numero'];
                $document_type_id = $row['codigo_tipo_documento'];
                $amount = $row['monto'];

                $prepayments[] = [
                    'number' => $number,
                    'document_type_id' => $document_type_id,
                    'amount' => $amount
                ];
            }
        }

        return $prepayments;
    }
}