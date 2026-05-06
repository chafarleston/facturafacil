<?php

namespace App\CoreFacturalo\Documents;

use App\Models\Document;

class DocumentBuilder
{
    public function saveDocument($data)
    {
        $document = Document::create($data);

        foreach ($data['items'] as $row) {
            $document->details()->create($row);
        }

        return $document;
    }
}