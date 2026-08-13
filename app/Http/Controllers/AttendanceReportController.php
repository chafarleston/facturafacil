<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Company;
use App\Models\Personal;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceReportController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('permission', 'view_attendance_reports');
        $companyId = Company::getMainCompany()->id;
        $personal = Personal::where('company_id', $companyId)->where('estado', 'ACTIVO')->with('schedule')->orderBy('apellidos')->get();

        $rango = $request->get('rango', 'mensual');
        $personalId = $request->get('personal_id');
        $fechaRef = $request->get('fecha', now()->toDateString());
        $mes = $request->get('mes', now()->format('Y-m'));

        $report = null;
        if ($request->filled('rango')) {
            $selected = $personalId ? $personal->where('id', (int) $personalId) : $personal;
            $range = $this->resolveRange($rango, $fechaRef, $mes);
            $report = $this->buildReport($companyId, $selected, $range['inicio'], $range['fin']);
        }

        return view('attendance.reports', compact('personal', 'rango', 'personalId', 'fechaRef', 'mes', 'report'));
    }

    public function exportPdf(Request $request)
    {
        $this->authorize('permission', 'view_attendance_reports');
        $companyId = Company::getMainCompany()->id;
        $personal = Personal::where('company_id', $companyId)->where('estado', 'ACTIVO')->with('schedule')->orderBy('apellidos')->get();

        $rango = $request->get('rango', 'mensual');
        $personalId = $request->get('personal_id');
        $fechaRef = $request->get('fecha', now()->toDateString());
        $mes = $request->get('mes', now()->format('Y-m'));

        $selected = $personalId ? $personal->where('id', (int) $personalId) : $personal;
        $range = $this->resolveRange($rango, $fechaRef, $mes);
        $report = $this->buildReport($companyId, $selected, $range['inicio'], $range['fin']);

        $pdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4', 'margin_top' => 10, 'margin_bottom' => 15]);
        $pdf->WriteHTML(view('attendance.report_pdf', compact('report', 'rango', 'range'))->render());
        return $pdf->Output('reporte-asistencia-' . now()->format('Ymd_His') . '.pdf', 'D');
    }

    public function exportExcel(Request $request)
    {
        $this->authorize('permission', 'view_attendance_reports');
        $companyId = Company::getMainCompany()->id;
        $personal = Personal::where('company_id', $companyId)->where('estado', 'ACTIVO')->with('schedule')->orderBy('apellidos')->get();

        $rango = $request->get('rango', 'mensual');
        $personalId = $request->get('personal_id');
        $fechaRef = $request->get('fecha', now()->toDateString());
        $mes = $request->get('mes', now()->format('Y-m'));

        $selected = $personalId ? $personal->where('id', (int) $personalId) : $personal;
        $range = $this->resolveRange($rango, $fechaRef, $mes);
        $report = $this->buildReport($companyId, $selected, $range['inicio'], $range['fin']);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Reporte Asistencia');

        $rows = [['DNI', 'Trabajador', 'Cargo', 'Fecha', 'Estado', 'Entrada 1', 'Salida 1', 'Entrada 2', 'Salida 2', 'Tardanza (min)', 'Descuento S/']];
        foreach ($report['trabajadores'] as $t) {
            foreach ($t['dias'] as $dia) {
                $rows[] = [
                    $t['dni'],
                    $t['nombre'],
                    $t['cargo'] ?? '',
                    $dia['fecha'],
                    $dia['estado'],
                    $dia['entrada_1'],
                    $dia['salida_1'],
                    $dia['entrada_2'],
                    $dia['salida_2'],
                    $dia['tardanza_min'],
                    $dia['descuento'],
                ];
            }
        }
        $sheet->fromArray($rows, null, 'A1');
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $temp = tempnam(sys_get_temp_dir(), 'asist');
        $writer->save($temp);
        return response()->download($temp, 'reporte-asistencia-' . now()->format('Ymd_His') . '.xlsx')->deleteFileAfterSend(true);
    }

    private function resolveRange(string $rango, string $fechaRef, string $mes): array
    {
        if ($rango === 'diario') {
            $day = Carbon::parse($fechaRef);
            return ['inicio' => $day->copy(), 'fin' => $day->copy()];
        }
        if ($rango === 'semanal') {
            $day = Carbon::parse($fechaRef);
            return ['inicio' => $day->copy()->startOfWeek(), 'fin' => $day->copy()->endOfWeek()];
        }
        $month = Carbon::parse($mes . '-01');
        return ['inicio' => $month->copy()->startOfMonth(), 'fin' => $month->copy()->endOfMonth()];
    }

    private function buildReport(int $companyId, $personal, Carbon $inicio, Carbon $fin): array
    {
        $service = app(AttendanceService::class);
        $trabajadores = [];

        foreach ($personal as $person) {
            $dias = [];
            $totFaltas = 0;
            $totTardanzas = 0;
            $totDescuento = 0;

            for ($date = $inicio->copy(); $date->lte($fin); $date->addDay()) {
                $att = $service->finalizeDay($person, $date->copy());

                if ($att->estado === 'FALTA') {
                    $totFaltas++;
                }
                if ($att->estado === 'TARDANZA') {
                    $totTardanzas++;
                }
                $totDescuento += (float) $att->descuento;

                $dias[] = [
                    'fecha' => $date->format('Y-m-d'),
                    'estado' => $att->estado,
                    'estado_label' => $att->estado_label,
                    'entrada_1' => $att->entrada_1 ? \Carbon\Carbon::parse($att->entrada_1)->format('H:i') : '-',
                    'salida_1' => $att->salida_1 ? \Carbon\Carbon::parse($att->salida_1)->format('H:i') : '-',
                    'entrada_2' => $att->entrada_2 ? \Carbon\Carbon::parse($att->entrada_2)->format('H:i') : '-',
                    'salida_2' => $att->salida_2 ? \Carbon\Carbon::parse($att->salida_2)->format('H:i') : '-',
                    'tardanza_min' => (int) $att->tardanza_min,
                    'descuento' => (float) $att->descuento,
                ];
            }

            $trabajadores[] = [
                'id' => $person->id,
                'dni' => $person->dni,
                'nombre' => $person->nombre_completo,
                'cargo' => $person->cargo,
                'sueldo' => (float) $person->sueldo,
                'dias' => $dias,
                'tot_faltas' => $totFaltas,
                'tot_tardanzas' => $totTardanzas,
                'tot_descuento' => round($totDescuento, 2),
            ];
        }

        return ['trabajadores' => $trabajadores, 'desde' => $inicio->format('Y-m-d'), 'hasta' => $fin->format('Y-m-d')];
    }
}
