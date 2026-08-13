<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Company;
use App\Models\Personal;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function markView()
    {
        $setting = \App\Models\AttendanceSetting::forCompany(Company::getMainCompany()->id);
        $modo = $setting->modo_marcacion ?? 'dni';
        $exitoSegundos = (int) ($setting->exito_segundos ?? 20);
        return view('attendance.mark', compact('modo', 'exitoSegundos'));
    }

    public function mark(Request $request)
    {
        $validated = $request->validate([
            'dni' => 'nullable|digits:8',
            'foto_base64' => 'nullable|string',
            'descriptor' => 'nullable|array',
            'descriptor.*' => 'numeric',
        ]);

        $companyId = Company::getMainCompany()->id;
        $service = app(AttendanceService::class);
        $setting = \App\Models\AttendanceSetting::forCompany($companyId);
        $modo = $setting->modo_marcacion ?? 'dni';
        $now = Carbon::now();

        $foto = $this->saveBase64Image($validated['foto_base64'] ?? null, 'marcaciones');
        $meta = ['foto' => $foto, 'verificado' => null, 'distancia' => null];

        $personal = null;

        if ($modo === 'webcam') {
            // Solo cámara: identificar por rostro (sin DNI)
            if (empty($validated['descriptor'])) {
                return response()->json(['success' => false, 'message' => 'No se detectó un rostro. Mire a la cámara y reintente.']);
            }
            $personal = $service->identifyFace($companyId, $validated['descriptor']);
            if (!$personal) {
                $service->recordRejectedAttempt(null, $now, ['foto' => $foto, 'distancia' => null]);
                return response()->json(['success' => false, 'message' => 'Rostro no reconocido. Intento registrado para revisión de administración.']);
            }
            $verification = $service->verifyFace($personal, $validated['descriptor']);
            $meta['verificado'] = true;
            $meta['distancia'] = $verification['distance'];
        } else {
            // Modos con DNI: dni (solo DNI) o dni_webcam (DNI + verificación facial)
            if (empty($validated['dni'])) {
                return response()->json(['success' => false, 'message' => 'Ingrese su DNI']);
            }
            $personal = Personal::where('company_id', $companyId)
                ->where('dni', $validated['dni'])
                ->where('estado', 'ACTIVO')
                ->first();

            if (!$personal) {
                return response()->json(['success' => false, 'message' => 'DNI no registrado o trabajador inactivo']);
            }

            if ($modo === 'dni_webcam' && !empty($validated['descriptor'])) {
                $verification = $service->verifyFace($personal, $validated['descriptor']);
                if (!$verification['verified']) {
                    $service->recordRejectedAttempt($personal, $now, [
                        'foto' => $foto,
                        'distancia' => $verification['distance'],
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'El rostro no coincide con el DNI ingresado. Intento registrado para revisión de administración.',
                        'distancia' => $verification['distance'],
                    ]);
                }
                $meta['verificado'] = true;
                $meta['distancia'] = $verification['distance'];
            }
        }

        try {
            $result = $service->recordMark($personal, $now, $meta);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    private function saveBase64Image(?string $base64, string $dir): ?string
    {
        if (!$base64 || !str_contains($base64, 'base64,')) {
            return null;
        }

        $ext = 'png';
        if (str_contains($base64, 'data:image/jpeg') || str_contains($base64, 'data:image/jpg')) {
            $ext = 'jpg';
        } elseif (str_contains($base64, 'data:image/webp')) {
            $ext = 'webp';
        }

        $decoded = base64_decode(explode('base64,', $base64)[1], true);
        if ($decoded === false || $decoded === '') {
            return null;
        }

        $name = $dir . '/' . date('Ymd') . '/' . uniqid() . '.' . $ext;
        $path = storage_path('app/public/' . $name);
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        file_put_contents($path, $decoded);

        return $name;
    }

    public function logs(Request $request)
    {
        $this->authorize('permission', 'view_attendance');
        $companyId = Company::getMainCompany()->id;
        $personalId = $request->get('personal_id');
        $fecha = $request->get('fecha');

        $query = AttendanceLog::where('company_id', $companyId)->with('personal')->orderByDesc('marcado_en');
        if ($personalId) {
            $query->where('personal_id', $personalId);
        }
        if ($fecha) {
            $query->whereDate('fecha', $fecha);
        }
        $logs = $query->paginate(30);
        $personal = Personal::where('company_id', $companyId)->where('estado', 'ACTIVO')->orderBy('apellidos')->get();

        return view('attendance.logs', compact('logs', 'personal', 'personalId', 'fecha'));
    }

    public function destroyLog(AttendanceLog $attendanceLog)
    {
        $this->authorize('permission', 'view_attendance');
        $attendanceLog->delete();
        return back()->with('success', 'Marcación eliminada');
    }
}
