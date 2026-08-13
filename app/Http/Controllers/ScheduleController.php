<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index()
    {
        $this->authorize('permission', 'view_schedules');
        $companyId = Company::getMainCompany()->id;
        $schedules = Schedule::where('company_id', $companyId)->orderBy('name')->get();
        return view('schedules.index', compact('schedules', 'companyId'));
    }

    public function create()
    {
        $this->authorize('permission', 'create_schedules');
        return view('schedules.create');
    }

    public function store(Request $request)
    {
        $this->authorize('permission', 'create_schedules');
        $companyId = Company::getMainCompany()->id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tipo' => 'required|in:corrido,dividido',
            'entrada_1' => 'nullable|date_format:H:i',
            'salida_1' => 'nullable|date_format:H:i',
            'entrada_2' => 'nullable|date_format:H:i',
            'salida_2' => 'nullable|date_format:H:i',
            'tolerancia_1' => 'nullable|integer|min:0|max:120',
            'tolerancia_2' => 'nullable|integer|min:0|max:120',
            'dias_laborables' => 'nullable|array',
            'dias_laborables.*' => 'integer|between:1,7',
            'estado' => 'nullable|in:ACTIVO,INACTIVO',
        ]);

        Schedule::create([
            'company_id' => $companyId,
            'name' => $validated['name'],
            'tipo' => $validated['tipo'],
            'entrada_1' => $validated['entrada_1'] ?? null,
            'salida_1' => $validated['salida_1'] ?? null,
            'entrada_2' => $validated['tipo'] === 'dividido' ? ($validated['entrada_2'] ?? null) : null,
            'salida_2' => $validated['tipo'] === 'dividido' ? ($validated['salida_2'] ?? null) : null,
            'tolerancia_1' => $validated['tolerancia_1'] ?? 0,
            'tolerancia_2' => $validated['tolerancia_2'] ?? 0,
            'dias_laborables' => $validated['dias_laborables'] ?? null,
            'estado' => $validated['estado'] ?? 'ACTIVO',
        ]);

        return redirect()->route('schedules.index')->with('success', 'Horario creado correctamente');
    }

    public function edit(Schedule $schedule)
    {
        $this->authorize('permission', 'edit_schedules');
        return view('schedules.edit', compact('schedule'));
    }

    public function update(Request $request, Schedule $schedule)
    {
        $this->authorize('permission', 'edit_schedules');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tipo' => 'required|in:corrido,dividido',
            'entrada_1' => 'nullable|date_format:H:i',
            'salida_1' => 'nullable|date_format:H:i',
            'entrada_2' => 'nullable|date_format:H:i',
            'salida_2' => 'nullable|date_format:H:i',
            'tolerancia_1' => 'nullable|integer|min:0|max:120',
            'tolerancia_2' => 'nullable|integer|min:0|max:120',
            'dias_laborables' => 'nullable|array',
            'dias_laborables.*' => 'integer|between:1,7',
            'estado' => 'nullable|in:ACTIVO,INACTIVO',
        ]);

        $schedule->update([
            'name' => $validated['name'],
            'tipo' => $validated['tipo'],
            'entrada_1' => $validated['entrada_1'] ?? null,
            'salida_1' => $validated['salida_1'] ?? null,
            'entrada_2' => $validated['tipo'] === 'dividido' ? ($validated['entrada_2'] ?? null) : null,
            'salida_2' => $validated['tipo'] === 'dividido' ? ($validated['salida_2'] ?? null) : null,
            'tolerancia_1' => $validated['tolerancia_1'] ?? 0,
            'tolerancia_2' => $validated['tolerancia_2'] ?? 0,
            'dias_laborables' => $validated['dias_laborables'] ?? null,
            'estado' => $validated['estado'] ?? 'ACTIVO',
        ]);

        return redirect()->route('schedules.index')->with('success', 'Horario actualizado correctamente');
    }

    public function destroy(Schedule $schedule)
    {
        $this->authorize('permission', 'delete_schedules');
        $schedule->delete();
        return redirect()->route('schedules.index')->with('success', 'Horario eliminado');
    }
}
