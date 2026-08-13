<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceDiscountRule;
use App\Models\AttendanceLog;
use App\Models\AttendanceSetting;
use App\Models\Personal;
use App\Models\Schedule;
use Carbon\Carbon;

class AttendanceService
{
    public function getOrCreate(Personal $personal, Carbon $date): Attendance
    {
        return Attendance::firstOrCreate(
            [
                'company_id' => $personal->company_id,
                'personal_id' => $personal->id,
                'fecha' => $date->toDateString(),
            ],
            ['estado' => 'PUNTUAL', 'tardanza_min' => 0, 'descuento' => 0]
        );
    }

    public function recordMark(Personal $personal, Carbon $now, array $meta = [])
    {
        $schedule = $personal->schedule;
        if (!$schedule) {
            return ['success' => false, 'message' => 'El trabajador no tiene horario asignado'];
        }

        $attendance = $this->getOrCreate($personal, $now->copy());

        if (!$schedule->isWorkingDay($now)) {
            $attendance->update(['estado' => 'DESCANSO', 'descuento' => 0, 'tardanza_min' => 0]);
            return [
                'success' => true,
                'event' => 'DESCANSO',
                'nombre' => $personal->nombre_completo,
                'hora' => $now->format('H:i:s'),
                'estado' => 'DESCANSO',
                'message' => 'Hoy no es día laborable para este horario',
            ];
        }

        $sequence = $schedule->eventSequence();
        $markedEvents = AttendanceLog::where('personal_id', $personal->id)
            ->whereDate('fecha', $now->toDateString())
            ->pluck('tipo_evento')
            ->all();

        $slot = null;
        foreach ($sequence as $candidate) {
            if (!in_array($candidate['event'], $markedEvents)) {
                $slot = $candidate;
                break;
            }
        }

        if (!$slot) {
            return [
                'success' => true,
                'event' => 'COMPLETADO',
                'nombre' => $personal->nombre_completo,
                'hora' => $now->format('H:i:s'),
                'estado' => 'COMPLETADO',
                'message' => 'Ya completó todas sus marcaciones del día',
            ];
        }

        if ($personal->suspendido && in_array($slot['event'], ['ENTRADA1', 'ENTRADA2'])) {
            return [
                'success' => false,
                'message' => 'Comuníquese a Administración. ' . $personal->nombre_completo . ' está suspendido y no puede realizar la marcación de entrada.',
            ];
        }

        AttendanceLog::create([
            'company_id' => $personal->company_id,
            'personal_id' => $personal->id,
            'fecha' => $now->toDateString(),
            'marcado_en' => $now,
            'tipo_evento' => $slot['event'],
            'foto' => $meta['foto'] ?? null,
            'verificado' => $meta['verificado'] ?? true,
            'distancia' => $meta['distancia'] ?? null,
        ]);

        $time = $now->format('H:i:s');
        $this->applyMarkToAttendance($attendance, $slot['event'], $time);

        $isEntry = in_array($slot['event'], ['ENTRADA1', 'ENTRADA2']);
        $state = null;
        $tardanza = 0;

        if ($isEntry) {
            $tolerance = $slot['event'] === 'ENTRADA1' ? $schedule->tolerancia_1 : $schedule->tolerancia_2;
            $entryTime = $this->timeToCarbon($now, $slot['time']);
            $lateMinutes = max(0, (int) floor(($now->getTimestamp() - $entryTime->getTimestamp()) / 60));
            $tardanza = max(0, $lateMinutes - (int) $tolerance);

            $attendance->tardanza_min += $tardanza;

            $setting = AttendanceSetting::forCompany($personal->company_id);
            if ($tardanza > (int) $setting->falta_grave_threshold_min) {
                $state = 'FALTA_GRAVE';
            } elseif ($tardanza > (int) $setting->falta_threshold_min) {
                $state = 'FALTA';
            } elseif ($tardanza > 0) {
                $state = 'TARDANZA';
            }
        }

        if ($state) {
            $attendance->estado = $state;
        }
        $attendance->save();

        $attendance->descuento = $this->computeDiscount($personal, $attendance);
        $attendance->save();

        $fresh = $attendance->fresh();
        if ($fresh->estado === 'FALTA_GRAVE' && !$personal->suspendido) {
            $setting = AttendanceSetting::forCompany($personal->company_id);
            if ($this->consecutiveGraveDays($personal, $now->copy()) >= (int) $setting->suspension_graves_count) {
                $personal->update(['suspendido' => true]);
                $fresh = $attendance->fresh();
            }
        }

        $estadoFinal = $fresh->estado;
        $estadoLabel = match ($estadoFinal) {
            'TARDANZA' => "TARDANZA — {$fresh->tardanza_min} min",
            'FALTA_GRAVE' => 'FALTA GRAVE',
            default => $estadoFinal,
        };

        return [
            'success' => true,
            'event' => $slot['event'],
            'nombre' => $personal->nombre_completo,
            'hora' => $now->format('H:i:s'),
            'estado' => $estadoLabel,
            'descuento' => $fresh->descuento,
        ];
    }

    private function applyMarkToAttendance(Attendance $attendance, string $event, string $time): void
    {
        $field = match ($event) {
            'ENTRADA1' => 'entrada_1',
            'SALIDA1' => 'salida_1',
            'ENTRADA2' => 'entrada_2',
            'SALIDA2' => 'salida_2',
            default => null,
        };
        if ($field && empty($attendance->{$field})) {
            $attendance->{$field} = $time;
        }
    }

    public function verifyFace(Personal $personal, array $descriptor): array
    {
        $stored = $personal->face_descriptor;
        if (empty($stored) || empty($descriptor)) {
            return ['verified' => false, 'distance' => null];
        }

        $stored = array_values((array) $stored);
        $current = array_values($descriptor);
        $distance = 0.0;
        $count = min(count($stored), count($current));
        for ($i = 0; $i < $count; $i++) {
            $diff = (float) $stored[$i] - (float) $current[$i];
            $distance += $diff * $diff;
        }

        return ['verified' => sqrt($distance) <= (float) AttendanceSetting::forCompany($personal->company_id)->reconocimiento_umbral, 'distance' => round(sqrt($distance), 4)];
    }

    public function identifyFace(int $companyId, array $descriptor): ?Personal
    {
        $threshold = (float) AttendanceSetting::forCompany($companyId)->reconocimiento_umbral;
        $best = null;
        $bestDistance = PHP_FLOAT_MAX;

        $personal = Personal::where('company_id', $companyId)
            ->where('estado', 'ACTIVO')
            ->whereNotNull('face_descriptor')
            ->get();

        foreach ($personal as $person) {
            $result = $this->verifyFace($person, $descriptor);
            if ($result['distance'] !== null && $result['distance'] < $bestDistance) {
                $bestDistance = $result['distance'];
                $best = $person;
            }
        }

        return ($best && $bestDistance <= $threshold) ? $best : null;
    }

    public function recordRejectedAttempt(?Personal $personal, Carbon $now, array $meta = []): void
    {
        AttendanceLog::create([
            'company_id' => $personal?->company_id ?? \App\Models\Company::getMainCompany()->id,
            'personal_id' => $personal?->id,
            'fecha' => $now->toDateString(),
            'marcado_en' => $now,
            'tipo_evento' => 'RECHAZADO',
            'foto' => $meta['foto'] ?? null,
            'verificado' => false,
            'distancia' => $meta['distancia'] ?? null,
        ]);
    }

    public function computeDiscount(Personal $personal, Attendance $attendance): float
    {
        if (in_array($attendance->estado, ['FALTA', 'FALTA_GRAVE'])) {
            $setting = AttendanceSetting::forCompany($personal->company_id);
            return $this->applyDiscount($setting->descuento_falta_tipo, (float) $setting->descuento_falta_valor, $personal->sueldo);
        }

        if ($attendance->estado === 'TARDANZA') {
            $rule = AttendanceDiscountRule::forMinutes($personal->company_id, (int) $attendance->tardanza_min);
            if (!$rule) {
                return 0;
            }
            return $this->applyDiscount($rule->tipo, (float) $rule->valor, $personal->sueldo);
        }

        return 0;
    }

    private function applyDiscount(string $tipo, float $valor, float $sueldo): float
    {
        if ($tipo === 'PORCENTAJE') {
            $sueldoDiario = $sueldo / 30;
            return round($sueldoDiario * $valor / 100, 2);
        }
        return round($valor, 2);
    }

    public function finalizeDay(Personal $personal, Carbon $date): Attendance
    {
        $attendance = $this->getOrCreate($personal, $date->copy());
        $schedule = $personal->schedule;

        if (!$schedule) {
            return $attendance;
        }

        if (!$schedule->isWorkingDay($date)) {
            if ($attendance->estado !== 'DESCANSO') {
                $attendance->update(['estado' => 'DESCANSO', 'descuento' => 0, 'tardanza_min' => 0]);
            }
            return $attendance;
        }

        $hasEntry = !empty($attendance->entrada_1)
            || ($schedule->tipo === 'dividido' && !empty($attendance->entrada_2));

        if (!$hasEntry) {
            $endOfShift = $this->endOfShift($schedule, $date);
            $finalized = $date->lt(Carbon::today()) || ($date->isToday() && Carbon::now()->gte($endOfShift));
            if ($finalized) {
                if ($attendance->estado !== 'FALTA') {
                    $attendance->update(['estado' => 'FALTA', 'tardanza_min' => 0, 'descuento' => 0]);
                }
                $attendance->descuento = $this->computeDiscount($personal, $attendance->fresh());
                $attendance->save();
            }
            return $attendance;
        }

        $this->recomputeStateFromTardanza($personal, $attendance);
        return $attendance;
    }

    private function recomputeStateFromTardanza(Personal $personal, Attendance $attendance): void
    {
        $setting = AttendanceSetting::forCompany($personal->company_id);
        $tardanza = (int) $attendance->tardanza_min;

        if ($tardanza > (int) $setting->falta_grave_threshold_min) {
            $attendance->estado = 'FALTA_GRAVE';
        } elseif ($tardanza > (int) $setting->falta_threshold_min) {
            $attendance->estado = 'FALTA';
        } elseif ($tardanza > 0) {
            $attendance->estado = 'TARDANZA';
        } else {
            $attendance->estado = 'PUNTUAL';
        }

        $attendance->descuento = $this->computeDiscount($personal, $attendance);
        $attendance->save();
    }

    private function consecutiveGraveDays(Personal $personal, Carbon $date): int
    {
        $schedule = $personal->schedule;
        if (!$schedule) {
            return 0;
        }

        $count = 0;
        $current = $date->copy();
        for ($i = 0; $i < 365; $i++) {
            if (!$schedule->isWorkingDay($current)) {
                $current->subDay();
                continue;
            }
            $att = Attendance::where('personal_id', $personal->id)
                ->whereDate('fecha', $current->toDateString())
                ->first();
            if ($att && $att->estado === 'FALTA_GRAVE') {
                $count++;
                $current->subDay();
            } else {
                break;
            }
        }
        return $count;
    }

    private function endOfShift(Schedule $schedule, Carbon $date): Carbon
    {
        $lastExit = $schedule->tipo === 'dividido' ? $schedule->salida_2 : $schedule->salida_1;
        return $this->timeToCarbon($date, $lastExit, '18:00:00');
    }

    private function timeToCarbon(Carbon $date, ?string $time, string $default = '00:00:00'): Carbon
    {
        $time = trim((string) ($time ?: $default));
        if (strlen($time) === 5) {
            $time .= ':00';
        }
        return Carbon::createFromFormat('Y-m-d H:i:s', $date->toDateString() . ' ' . $time);
    }
}
