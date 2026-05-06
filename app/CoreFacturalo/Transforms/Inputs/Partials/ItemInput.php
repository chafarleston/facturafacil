<?php

namespace App\CoreFacturalo\Transforms\Inputs\Partials;

class ItemInput
{
    public static function transform($inputs)
    {
        $items = [];
        foreach ($inputs['items'] as $row)
        {
            $attributes = ItemAttributeInput::transform($row);
            $charges = ChargeInput::transform($row);
            $discounts = DiscountInput::transform($row);

            $item = [
                'description' => $row['descripcion'],
                'item_type_id' => '01',
                'internal_id' => array_key_exists('codigo_interno', $row)?$row['codigo_interno']:null,
                'item_code' => array_key_exists('codigo_producto_sunat', $row)?$row['codigo_producto_sunat']:null,
                'item_code_gs1' => array_key_exists('codigo_producto_gsl', $row)?$row['codigo_producto_gsl']:null,
                'unit_type_id' => strtoupper($row['unidad_de_medida']),
            ];

            $quantity = $row['cantidad'];
            $unit_value = $row['valor_unitario'];
            $price_type_id = $row['codigo_tipo_precio'];
            $unit_price = $row['precio_unitario'];
            $affectation_igv_type_id = $row['codigo_tipo_afectacion_igv'];
            $total_base_igv = $row['total_base_igv'];
            $percentage_igv = $row['porcentaje_igv'];
            $total_igv = $row['total_igv'];
            $system_isc_type_id = array_key_exists('codigo_tipo_sistema_isc', $row)?$row['codigo_tipo_sistema_isc']:null;
            $total_base_isc = array_key_exists('total_base_isc', $row)?$row['total_base_isc']:0;
            $percentage_isc = array_key_exists('porcentaje_isc', $row)?$row['porcentaje_isc']:0;
            $total_isc = array_key_exists('total_isc', $row)?$row['total_isc']:0;
            $total_base_other_taxes = array_key_exists('total_base_otros_impuestos', $row)?$row['total_base_otros_impuestos']:0;
            $percentage_other_taxes = array_key_exists('porcentaje_otros_impuestos', $row)?$row['porcentaje_otros_impuestos']:0;
            $total_other_taxes = array_key_exists('total_otros_impuestos', $row)?$row['total_otros_impuestos']:0;
            $total_taxes = $row['total_impuestos'];
            $total_value = $row['total_valor_item'];
            $total = $row['total_item'];

            $items[] = [
                'item' => $item,
                'quantity' => $quantity,
                'unit_value' => $unit_value,
                'price_type_id' => $price_type_id,
                'unit_price' => $unit_price,

                'affectation_igv_type_id' => $affectation_igv_type_id,
                'total_base_igv' => $total_base_igv,
                'percentage_igv' => $percentage_igv,
                'total_igv' => $total_igv,

                'system_isc_type_id' => $system_isc_type_id,
                'total_base_isc' => $total_base_isc,
                'percentage_isc' => $percentage_isc,
                'total_isc' => $total_isc,

                'total_base_other_taxes' => $total_base_other_taxes,
                'percentage_other_taxes' => $percentage_other_taxes,
                'total_other_taxes' => $total_other_taxes,

                'total_taxes' => $total_taxes,
                'total_value' => $total_value,
                'total' => $total,

                'attributes' => $attributes,
                'discounts' => $discounts,
                'charges' => $charges,
            ];
        }

        return $items;
    }
}