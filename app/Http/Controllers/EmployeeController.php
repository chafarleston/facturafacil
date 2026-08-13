<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Personal;
use App\Models\Schedule;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('permission', 'view_personal');
        $companyId = Company::getMainCompany()->id;
        $personal = Personal::where('company_id', $companyId)->with('schedule')->orderBy('apellidos')->get();
        return view('personal.index', compact('personal', 'companyId'));
    }

    public function create()
    {
        $this->authorize('permission', 'create_personal');
        $companyId = Company::getMainCompany()->id;
        $schedules = Schedule::where('company_id', $companyId)->where('estado', 'ACTIVO')->orderBy('name')->get();
        return view('personal.create', compact('companyId', 'schedules'));
    }

    public function store(Request $request)
    {
        $this->authorize('permission', 'create_personal');
        $companyId = Company::getMainCompany()->id;

        $validated = $request->validate([
            'dni' => 'required|digits:8',
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'cargo' => 'nullable|string|max:255',
            'sueldo' => 'nullable|numeric|min:0',
            'schedule_id' => 'nullable|exists:schedules,id',
            'estado' => 'nullable|in:ACTIVO,INACTIVO',
            'foto' => 'nullable|image|max:2048',
            'face_descriptor' => 'nullable|string',
        ]);

        $exists = Personal::where('company_id', $companyId)->where('dni', $validated['dni'])->exists();
        if ($exists) {
            return back()->with('error', 'Ya existe un trabajador con ese DNI')->withInput();
        }

        $foto = $request->hasFile('foto') ? $request->file('foto')->store('personal', 'public') : null;
        $faceDescriptor = $this->decodeDescriptor($request->input('face_descriptor'));

        Personal::create([
            'company_id' => $companyId,
            'dni' => $validated['dni'],
            'nombres' => $validated['nombres'],
            'apellidos' => $validated['apellidos'],
            'cargo' => $validated['cargo'] ?? null,
            'sueldo' => $validated['sueldo'] ?? 0,
            'schedule_id' => $validated['schedule_id'] ?? null,
            'estado' => $validated['estado'] ?? 'ACTIVO',
            'foto' => $foto,
            'face_descriptor' => $faceDescriptor,
        ]);

        return redirect()->route('personal.index')->with('success', 'Trabajador registrado correctamente');
    }

    public function edit(Personal $personal)
    {
        $this->authorize('permission', 'edit_personal');
        $schedules = Schedule::where('company_id', $personal->company_id)->where('estado', 'ACTIVO')->orderBy('name')->get();
        return view('personal.edit', compact('personal', 'schedules'));
    }

    public function update(Request $request, Personal $personal)
    {
        $this->authorize('permission', 'edit_personal');

        $validated = $request->validate([
            'dni' => 'required|digits:8',
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'cargo' => 'nullable|string|max:255',
            'sueldo' => 'nullable|numeric|min:0',
            'schedule_id' => 'nullable|exists:schedules,id',
            'estado' => 'nullable|in:ACTIVO,INACTIVO',
            'foto' => 'nullable|image|max:2048',
            'face_descriptor' => 'nullable|string',
        ]);

        $exists = Personal::where('company_id', $personal->company_id)
            ->where('dni', $validated['dni'])
            ->where('id', '!=', $personal->id)
            ->exists();
        if ($exists) {
            return back()->with('error', 'Ya existe otro trabajador con ese DNI')->withInput();
        }

        $foto = $personal->foto;
        if ($request->hasFile('foto')) {
            $foto = $request->file('foto')->store('personal', 'public');
        }
        $faceDescriptor = $request->filled('face_descriptor')
            ? $this->decodeDescriptor($request->input('face_descriptor'))
            : $personal->face_descriptor;

        $personal->update([
            'dni' => $validated['dni'],
            'nombres' => $validated['nombres'],
            'apellidos' => $validated['apellidos'],
            'cargo' => $validated['cargo'] ?? null,
            'sueldo' => $validated['sueldo'] ?? 0,
            'schedule_id' => $validated['schedule_id'] ?? null,
            'estado' => $validated['estado'] ?? 'ACTIVO',
            'foto' => $foto,
            'face_descriptor' => $faceDescriptor,
        ]);

        return redirect()->route('personal.index')->with('success', 'Trabajador actualizado correctamente');
    }

    private function decodeDescriptor(?string $json): ?array
    {
        if (!$json || !str_contains($json, '[')) {
            return null;
        }
        $decoded = json_decode($json, true);
        return is_array($decoded) && count($decoded) > 0 ? $decoded : null;
    }

    public function destroy(Personal $personal)
    {
        $this->authorize('permission', 'delete_personal');
        $personal->delete();
        return redirect()->route('personal.index')->with('success', 'Trabajador eliminado');
    }

    public function toggleSuspension(Personal $personal)
    {
        $this->authorize('permission', 'edit_personal');
        $personal->update(['suspendido' => !$personal->suspendido]);
        return redirect()->route('personal.index')
            ->with('success', $personal->suspendido
                ? 'Trabajador suspendido'
                : 'Suspensión levantada correctamente');
    }
}
