<?php

namespace App\Http\Controllers;

use App\Models\CashReportSetting;
use App\Models\Company;
use Illuminate\Http\Request;

class CashReportSettingController extends Controller
{
    public function edit(Request $request)
    {
        $this->authorize('permission', 'manage_report_settings');

        $companyId = $request->company_id ?? Company::getMainCompany()->id;
        $config = CashReportSetting::where('company_id', $companyId)->first();

        return view('cashreportsettings.edit', compact('companyId', 'config'));
    }

    public function update(Request $request)
    {
        $this->authorize('permission', 'manage_report_settings');

        $companyId = $request->company_id ?? Company::getMainCompany()->id;

        $validated = $request->validate([
            'mostrar_lista_comprobantes' => 'nullable|boolean',
            'mostrar_productos_vendidos' => 'nullable|boolean',
            'mostrar_lineas_eliminadas' => 'nullable|boolean',
        ]);

        CashReportSetting::updateOrCreate(
            ['company_id' => $companyId],
            [
                'mostrar_lista_comprobantes' => $request->boolean('mostrar_lista_comprobantes'),
                'mostrar_productos_vendidos' => $request->boolean('mostrar_productos_vendidos'),
                'mostrar_lineas_eliminadas' => $request->boolean('mostrar_lineas_eliminadas'),
            ]
        );

        return redirect()->route('cash-report-settings.edit', ['company_id' => $companyId])
            ->with('success', 'Configuración de reporte guardada correctamente.');
    }
}