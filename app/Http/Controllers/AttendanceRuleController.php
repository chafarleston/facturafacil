<?php

namespace App\Http\Controllers;

use App\Models\AttendanceDiscountRule;
use App\Models\AttendanceSetting;
use App\Models\Company;
use Illuminate\Http\Request;

class AttendanceRuleController extends Controller
{
    public function index()
    {
        $this->authorize('permission', 'view_attendance_rules');
        $companyId = Company::getMainCompany()->id;

        $setting = AttendanceSetting::forCompany($companyId);
        AttendanceDiscountRule::seedDefaults($companyId);
        $rules = AttendanceDiscountRule::where('company_id', $companyId)->orderBy('tardanza_min')->get();

        return view('attendance_rules.index', compact('setting', 'rules', 'companyId'));
    }

    public function update(Request $request)
    {
        $this->authorize('permission', 'edit_attendance_rules');
        $companyId = Company::getMainCompany()->id;

        $validated = $request->validate([
            'falta_threshold_min' => 'required|integer|min:1|max:600',
            'falta_grave_threshold_min' => 'required|integer|min:1|max:600',
            'suspension_graves_count' => 'required|integer|min:1|max:30',
            'descuento_falta_tipo' => 'required|in:FIJO,PORCENTAJE',
            'descuento_falta_valor' => 'required|numeric|min:0',
            'modo_marcacion' => 'required|in:dni,webcam,dni_webcam',
            'reconocimiento_umbral' => 'required|numeric|min:0.1|max:1',
            'exito_segundos' => 'required|integer|min:5|max:120',
            'rules' => 'nullable|array',
            'rules.*.tipo' => 'required|in:FIJO,PORCENTAJE',
            'rules.*.valor' => 'required|numeric|min:0',
        ]);

        $setting = AttendanceSetting::forCompany($companyId);
        $setting->update([
            'falta_threshold_min' => $validated['falta_threshold_min'],
            'falta_grave_threshold_min' => $validated['falta_grave_threshold_min'],
            'suspension_graves_count' => $validated['suspension_graves_count'],
            'descuento_falta_tipo' => $validated['descuento_falta_tipo'],
            'descuento_falta_valor' => $validated['descuento_falta_valor'],
            'modo_marcacion' => $validated['modo_marcacion'],
            'reconocimiento_umbral' => $validated['reconocimiento_umbral'],
            'exito_segundos' => $validated['exito_segundos'],
        ]);

        foreach (($validated['rules'] ?? []) as $ruleId => $ruleData) {
            AttendanceDiscountRule::where('company_id', $companyId)
                ->where('id', $ruleId)
                ->update(['tipo' => $ruleData['tipo'], 'valor' => $ruleData['valor']]);
        }

        return redirect()->route('attendance-rules.index')->with('success', 'Reglas de tardanza y faltas guardadas');
    }
}
