<?php

namespace App\Http\Controllers;

use App\Models\SunatAlert;
use App\Services\SunatAlertService;

class SunatAlertController extends Controller
{
    public function resolve(SunatAlert $sunatAlert)
    {
        $this->authorize('permission', 'send_sunat');

        $sunatAlert->resolve();
        SunatAlertService::forgetCountCache();

        return back()->with('success', 'Alerta marcada como resuelta.');
    }
}