<?php

namespace App\CoreFacturalo\Documents;

class NoteBuilder extends DocumentBuilder
{
    public function save($inputs)
    {
        $document = array_key_exists('document', $inputs)?$inputs['document']:$inputs;
        $document = $this->saveDocument($document);

        $document_base = array_key_exists('document_base', $inputs)?$inputs['document_base']:$inputs;
        $document->note()->create($document_base);

        return $document;
    }
}