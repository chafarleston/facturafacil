<?php

namespace App\CoreFacturalo\Transforms\Inputs;

class InvoiceInput
{
    public static function transform($inputs, $document)
    {
        $date_of_due = $inputs['fecha_de_vencimiento'];
        $operation_type_id = $inputs['codigo_tipo_operacion'];

        return [
            'type' => 'invoice',
            'group_id' => ($document['document_type_id'] === '01')?'01':'02',
            'document_base' => [
                'date_of_due' => $date_of_due,
                'operation_type_id' => $operation_type_id
            ]
        ];
    }
}