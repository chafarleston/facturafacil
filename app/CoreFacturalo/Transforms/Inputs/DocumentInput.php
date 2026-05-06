<?php

namespace App\CoreFacturalo\Transforms\Inputs;

use App\CoreFacturalo\Transforms\Inputs\Partials\ActionInput;
use App\CoreFacturalo\Transforms\Inputs\Partials\ChargeInput;
use App\CoreFacturalo\Transforms\Inputs\Partials\CustomerInput;
use App\CoreFacturalo\Transforms\Inputs\Partials\DetractionInput;
use App\CoreFacturalo\Transforms\Inputs\Partials\DiscountInput;
use App\CoreFacturalo\Transforms\Inputs\Partials\EstablishmentInput;
use App\CoreFacturalo\Transforms\Inputs\Partials\GuideInput;
use App\CoreFacturalo\Transforms\Inputs\Partials\ItemInput;
use App\CoreFacturalo\Transforms\Inputs\Partials\LegendInput;
use App\CoreFacturalo\Transforms\Inputs\Partials\OptionalInput;
use App\CoreFacturalo\Transforms\Inputs\Partials\PerceptionInput;
use App\CoreFacturalo\Transforms\Inputs\Partials\PrepaymentInput;
use App\CoreFacturalo\Transforms\Inputs\Partials\RelatedInput;
use App\Models\Company;
use App\Models\Document;
use Exception;
use Illuminate\Support\Str;

class DocumentInput
{
    public static function transform($inputs)
    {
        $soap_type_id = Company::active()->soap_type_id;
        $document_type_id = $inputs['codigo_tipo_documento'];

        self::validateDocumentTypeId($document_type_id);

        $series = $inputs['serie_documento'];
        $number = self::number($soap_type_id, $document_type_id, $series, $inputs['numero_documento']);

        self::validateUniqueDocument($soap_type_id, $document_type_id, $series, $number);

        $date_of_issue = $inputs['fecha_de_emision'];
        $time_of_issue = $inputs['hora_de_emision'];
        $currency_type_id = $inputs['codigo_tipo_moneda'];
        $purchase_order = array_key_exists('numero_orden_de_compra', $inputs)?$inputs['numero_orden_de_compra']:null;

        $totals = $inputs['totales'];
        $total_prepayment = array_key_exists('total_anticipos', $totals)?$totals['total_anticipos']:0;
        $total_discount = array_key_exists('total_descuentos', $totals)?$totals['total_descuentos']:0;
        $total_charge = array_key_exists('total_cargos', $totals)?$totals['total_cargos']:0;
        $total_exportation = array_key_exists('total_exportacion', $totals)?$totals['total_exportacion']:0;
        $total_free = array_key_exists('total_operaciones_gratuitas', $totals)?$totals['total_operaciones_gratuitas']:0;
        $total_taxed = array_key_exists('total_operaciones_gravadas', $totals)?$totals['total_operaciones_gravadas']:0;
        $total_unaffected = array_key_exists('total_operaciones_inafectas', $totals)?$totals['total_operaciones_inafectas']:0;
        $total_exonerated = array_key_exists('total_operaciones_exoneradas', $totals)?$totals['total_operaciones_exoneradas']:0;
        $total_igv = array_key_exists('total_igv', $totals)?$totals['total_igv']:0;
        $total_base_isc = array_key_exists('total_base_isc', $totals)?$totals['total_base_isc']:0;
        $total_isc = array_key_exists('total_isc', $totals)?$totals['total_isc']:0;
        $total_base_other_taxes = array_key_exists('total_base_otros_impuestos', $totals)?$totals['total_base_otros_impuestos']:0;
        $total_other_taxes = array_key_exists('total_otros_impuestos', $totals)?$totals['total_otros_impuestos']:0;
        $total_taxes = array_key_exists('total_impuestos', $totals)?$totals['total_impuestos']:0;
        $total_value = array_key_exists('total_valor', $totals)?$totals['total_valor']:0;
        $total = $totals['total_venta'];

        $filename = self::filename($document_type_id, $series, $number);

        return [
            'actions' => ActionInput::transform($inputs),
            'document' => [
                'user_id' => auth()->id(),
                'external_id' => Str::uuid(),
                'establishment' => EstablishmentInput::transform($inputs),
                'soap_type_id' => $soap_type_id,
                'state_type_id' => '01',
                'ubl_version' => '2.1',
                'filename' => $filename,
                'group_id' => null,
                'document_type_id' => $document_type_id,
                'series' => $series,
                'number' => $number,
                'date_of_issue' => $date_of_issue,
                'time_of_issue' => $time_of_issue,
                'customer' => CustomerInput::transform($inputs),
                'currency_type_id' => $currency_type_id,
                'purchase_order' => $purchase_order,
                'total_prepayment' => $total_prepayment,
                'total_discount' => $total_discount,
                'total_charge' => $total_charge,
                'total_exportation' => $total_exportation,
                'total_free' => $total_free,
                'total_taxed' => $total_taxed,
                'total_unaffected' => $total_unaffected,
                'total_exonerated' => $total_exonerated,
                'total_igv' => $total_igv,
                'total_base_isc' => $total_base_isc,
                'total_isc' => $total_isc,
                'total_base_other_taxes' => $total_base_other_taxes,
                'total_other_taxes' => $total_other_taxes,
                'total_taxes' => $total_taxes,
                'total_value' => $total_value,
                'total' => $total,
                'charges' => ChargeInput::transform($inputs),
                'discounts' => DiscountInput::transform($inputs),
                'prepayments' => PrepaymentInput::transform($inputs),
                'guides' => GuideInput::transform($inputs),
                'related' => RelatedInput::transform($inputs),
                'perception' => PerceptionInput::transform($inputs),
                'detraction' => DetractionInput::transform($inputs),
                'legends' => LegendInput::transform($inputs),
                'extras' => OptionalInput::transform($inputs),
                'items' => ItemInput::transform($inputs)
            ]
        ];
    }

    private static function number($soap_type_id, $document_type_id, $series, $number)
    {
        if ($number === '#') {
            $document = Document::select('number')
                                ->where('soap_type_id', $soap_type_id)
                                ->where('document_type_id', $document_type_id)
                                ->where('series', $series)
                                ->where('user_id', auth()->id())
                                ->orderBy('number', 'desc')
                                ->first();
            $number = ($document)?(int)$document->number+1:1;
        }
        return $number;
    }

    private static function filename($document_type_id, $series, $number)
    {
        $company = Company::active();
        return join('-', [$company->number, $document_type_id, $series, $number]);
    }

    private static function validateDocumentTypeId($document_type_id)
    {
        if(!in_array($document_type_id, ['01', '03', '07', '08'])) {
            throw new Exception("El código tipo de documento {$document_type_id} es incorrecto.");
        }

        return true;
    }

    private static function validateUniqueDocument($soap_type_id, $document_type_id, $series, $number)
    {
        $document = Document::where('soap_type_id', $soap_type_id)
                            ->where('document_type_id', $document_type_id)
                            ->where('series', $series)
                            ->where('number', $number)
                            ->where('user_id', auth()->id())
                            ->first();
        if($document) {
            throw new Exception("El documento: {$document_type_id} {$series}-{$number} ya se encuentra registrado.");
        }

        return true;
    }
}