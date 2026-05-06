<?php

namespace App\CoreFacturalo\Transforms\Inputs;

use App\Models\Company;
use App\Models\Document;
use App\Models\Summary;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;

class SummaryInput
{
    public static function transform($inputs)
    {
        $soap_type_id = Company::getSoapTypeId();

        $date_of_reference = $inputs['fecha_de_emision_de_documentos'];
        $date_of_issue = date('Y-m-d');
        $process_type_id = $inputs['codigo_tipo_proceso'];
        $documents = array_key_exists('documentos', $inputs)?$inputs['documentos']:[];

        $identifier = self::identifier($soap_type_id, $date_of_issue);
        $filename = self::filename($identifier);
        if ($process_type_id === '1') {
            $documents = self::findDocuments($soap_type_id, $date_of_reference);
        } elseif ($process_type_id === '3') {
            $documents = self::verifyDocuments($soap_type_id, $date_of_reference, $documents);
        } else {
            throw new Exception("El código de tipo de proceso {$process_type_id} es inválido");
        }

        return [
            'user_id' => auth()->id(),
            'external_id' => Str::uuid(),
            'soap_type_id' => $soap_type_id,
            'state_type_id' => '01',
            'process_type_id' => $process_type_id,
            'ubl_version' => '2.0',
            'date_of_issue' => $date_of_issue,
            'date_of_reference' => $date_of_reference,
            'identifier' => $identifier,
            'filename' => $filename,
            'documents' => $documents
        ];
    }

    private static function identifier($soap_type_id, $date_of_issue)
    {
        $summaries = Summary::where('soap_type_id', $soap_type_id)
                            ->where('date_of_issue', $date_of_issue)
                            ->where('user_id', auth()->id())
                            ->get();
        $numeration = count($summaries) + 1;

        return join('-', ['RC', Carbon::parse($date_of_issue)->format('Ymd'), $numeration]);
    }

    private static function filename($identifier)
    {
        $company = Company::active();
        return $company->number.'-'.$identifier;
    }

    private static function findDocuments($soap_type_id, $date_of_reference)
    {
        $documents = Document::where('soap_type_id', $soap_type_id)
                            ->where('date_of_issue', $date_of_reference)
                            ->where('group_id', '02')
                            ->where('user_id', auth()->id())
                            ->get();

        if(count($documents) === 0) {
            throw new Exception("No se encontraron documentos con la fecha {$date_of_reference}");
        }
        $aux_documents = [];
        foreach ($documents as $doc)
        {
            $aux_documents[] = [
                'document_id' => $doc->id
            ];
        }

        return $aux_documents;
    }

    private static function verifyDocuments($soap_type_id, $date_of_reference, $documents)
    {
        $aux_documents = [];
        foreach ($documents as $doc)
        {
            $external_id = $doc['external_id'];
            $description = $doc['motivo_anulacion'];

            $document = Document::where('soap_type_id', $soap_type_id)
                                ->where('external_id', $external_id)
                                ->where('group_id', '02')
                                ->where('date_of_issue', $date_of_reference)
                                ->where('user_id', auth()->id())
                                ->first();
            if(!$document) {
                throw new Exception("El documento con codigo externo {$external_id} no es encontró");
            }
            $aux_documents[] = [
                'document_id' => $document->id,
                'description' => $description
            ];
        }
        if(count($aux_documents) === 0) {
            throw new Exception("No se enviaron documentos para la anulación");
        }

        return $aux_documents;
    }
}