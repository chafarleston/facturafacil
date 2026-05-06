<?php

namespace App\CoreFacturalo\Transforms\Inputs\Partials;

class GuideInput
{
    public static function transform($inputs)
    {
        $guides = null;
        if(key_exists('guias', $inputs)) {
            foreach ($inputs['guias'] as $row)
            {
                $number = $row['numero'];
                $document_type_id = $row['codigo_tipo_documento'];

                $guides[] = [
                    'number' => $number,
                    'document_type_id' => $document_type_id,
                ];
            }
        }

        return $guides;
    }
}