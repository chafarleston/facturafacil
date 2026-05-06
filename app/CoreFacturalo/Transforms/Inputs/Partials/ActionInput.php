<?php

namespace App\CoreFacturalo\Transforms\Inputs\Partials;

class ActionInput
{
    public static function transform($inputs)
    {
        if(key_exists('acciones', $inputs)) {
            $actions = $inputs['acciones'];
            $send_email = array_key_exists('enviar_email', $actions)?(bool)$actions['enviar_email']:false;
            $send_xml_signed = array_key_exists('enviar_xml_firmado', $actions)?(bool)$actions['enviar_xml_firmado']:true;

            return [
                'send_email' => $send_email,
                'send_xml_signed' => $send_xml_signed
            ];
        }

        return [
            'send_xml_signed' => true
        ];
    }
}