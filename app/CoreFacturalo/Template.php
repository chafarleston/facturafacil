<?php

namespace App\CoreFacturalo;

class Template
{
    public function pdf($template, $company, $document)
    {
        if($template === 'credit' || $template === 'debit') {
            $template = 'note';
        }
        return self::render('pdf.'.$template, $company, $document);
    }

    public function xml($template, $company, $document)
    {
        return self::render('xml.'.$template, $company, $document);
    }

    private function render($view, $company, $document)
    {
        view()->addLocation(__DIR__.'/Templates');
        return view($view, compact('company', 'document'))->render();
    }
}