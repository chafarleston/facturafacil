<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class PrecuadreSettingController extends Controller
{
    public function toggle(Request $request)
    {
        Setting::setPrecuadreEnabled(! Setting::isPrecuadreEnabled());

        $enabled = Setting::isPrecuadreEnabled();

        return redirect()->back()->with('success', $enabled
            ? 'Precuadre habilitado'
            : 'Precuadre deshabilitado');
    }
}
