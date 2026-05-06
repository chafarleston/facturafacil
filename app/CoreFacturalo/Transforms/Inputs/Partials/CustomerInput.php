<?php

namespace App\CoreFacturalo\Transforms\Inputs\Partials;

use App\Models\Catalogs\Country;
use App\Models\Catalogs\Department;
use App\Models\Catalogs\District;
use App\Models\Catalogs\IdentityDocumentType;
use App\Models\Catalogs\Province;

class CustomerInput
{
    public static function transform($inputs)
    {
        $customer = $inputs['datos_del_cliente_o_receptor'];

        $identity_document_type_id = $customer['codigo_tipo_documento_identidad'];
        $number = $customer['numero_documento'];
        $name = $customer['apellidos_y_nombres_o_razon_social'];
        $trade_name = array_key_exists('nombre_comercial', $customer)?$customer['nombre_comercial']:null;
        $country_id = array_key_exists('codigo_pais', $customer)?$customer['codigo_pais']:null;
        $district_id = array_key_exists('ubigeo', $customer)?$customer['ubigeo']:null;
        $address = array_key_exists('direccion', $customer)?$customer['direccion']:null;
        $email = array_key_exists('correo_electronico', $customer)?$customer['correo_electronico']:null;
        $telephone = array_key_exists('telephone', $customer)?$customer['telefono']:null;

        $department_id = null;
        $province_id = null;

        if ($district_id) {
            $province_id = substr($district_id, 0 ,4);
            $department_id = substr($district_id, 0 ,2);
        }

        return [
            'identity_document_type_id' => $identity_document_type_id,
            'identity_document_type' => [
                'id' => $identity_document_type_id,
                'description' => IdentityDocumentType::find($identity_document_type_id)->description,
            ],
            'number' => $number,
            'name' => $name,
            'trade_name' => $trade_name,
            'country_id' => $country_id,
            'country' => [
                'id' => $country_id,
                'description' => Country::find($country_id)->description,
            ],
            'department_id' => $department_id,
            'department' => [
                'id' => $department_id,
                'description' => Department::find($department_id)->description,
            ],
            'province_id' => $province_id,
            'province' => [
                'id' => $province_id,
                'description' => Province::find($province_id)->description,
            ],
            'district_id' => $district_id,
            'district' => [
                'id' => $district_id,
                'description' => District::find($district_id)->description,
            ],
            'address' => $address,
            'email' => $email,
            'telephone' => $telephone,
        ];
    }
}