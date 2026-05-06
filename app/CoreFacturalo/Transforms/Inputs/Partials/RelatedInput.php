<?php

namespace App\CoreFacturalo\Transforms\Inputs\Partials;

class RelatedInput
{
    public static function transform($inputs)
    {
        $related = null;
        if(key_exists('relacionados', $inputs)) {
            foreach ($inputs['relacionados'] as $row)
            {
                $number = $row['numero'];
                $document_type_id = $row['codigo_tipo_documento'];
                $amount = $row['monto'];

                $related[] = [
                    'number' => $number,
                    'document_type_id' => $document_type_id,
                    'amount' => $amount
                ];
            }
        }

        return $related;
    }
}