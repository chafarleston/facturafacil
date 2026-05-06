<?php

namespace App\CoreFacturalo\Documents;

use App\Models\Voided;

class VoidedBuilder
{
    public function save($inputs)
    {
        $data = array_key_exists('voided', $inputs)?$inputs['voided']:$inputs;
        $voided = Voided::create($data);

        foreach ($data['documents'] as $row) {
            $voided->details()->create($row);
        }

        return $voided;
    }
}