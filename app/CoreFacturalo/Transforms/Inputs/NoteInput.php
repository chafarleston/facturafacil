<?php

namespace App\CoreFacturalo\Transforms\Inputs;

use App\Models\Document;
use Exception;

class NoteInput
{
    public static function transform($inputs, $document)
    {
        $affected_document = $inputs['documento_afectado'];
        $affected_document_series = $affected_document['serie_documento'];
        $affected_document_number = $affected_document['numero_documento'];
        $affected_document_type_id = $affected_document['codigo_tipo_documento'];
        $note_credit_or_debit_type_id = $inputs['codigo_tipo_nota'];
        $description = $inputs['motivo_o_sustento_de_nota'];

        if ($document['document_type_id'] === '07') {
            $note_type = 'credit';
            $note_credit_type_id = $note_credit_or_debit_type_id;
            $note_debit_type_id = null;
            $type = 'credit';
        } else {
            $note_type = 'debit';
            $note_credit_type_id = null;
            $note_debit_type_id = $note_credit_or_debit_type_id;
            $type = 'debit';
        }

        $affected_document = self::findAffectedDocument($document['soap_type_id'], $affected_document_type_id, $affected_document_series, $affected_document_number);

        return [
            'type' => $type,
            'group_id' => ($affected_document_type_id === '01')?'01':'02',
            'document_base' => [
                'note_type' => $note_type,
                'note_credit_type_id' => $note_credit_type_id,
                'note_debit_type_id' => $note_debit_type_id,
                'description' => $description,
                'affected_document_id' => $affected_document->id
            ]
        ];
    }

    private static function findAffectedDocument($soap_type_id, $document_type_id, $series, $number)
    {
        $document = Document::where('soap_type_id', $soap_type_id)
                            ->where('document_type_id', $document_type_id)
                            ->where('series', $series)
                            ->where('number', $number)
                            ->first();
        if(!$document) {
            throw new Exception("El documento: {$series}-{$number} no se encuentra registrado.");
        }

        return $document;
    }
}