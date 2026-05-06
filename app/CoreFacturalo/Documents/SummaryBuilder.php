<?php

namespace App\CoreFacturalo\Documents;

use App\Models\Summary;

class SummaryBuilder
{
    public function save($inputs)
    {
        $data = array_key_exists('summary', $inputs)?$inputs['summary']:$inputs;
        $summary = Summary::create($data);

        foreach ($data['documents'] as $row) {
            $summary->details()->create($row);
        }

        return $summary;
    }
}