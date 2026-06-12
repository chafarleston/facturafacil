<?php

namespace App\Http\Controllers;

use App\Models\Serie;
use App\Models\Company;
use Illuminate\Http\Request;

class SerieController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->company_id ?? \App\Models\Company::getMainCompany()->id;
        $series = Serie::where('company_id', $companyId)->orderBy('tipo_documento')->orderBy('serie')->get();
        
        return view('series.index', compact('series', 'companyId'));
    }

    public function create(Request $request)
    {
        $companyId = $request->company_id ?? \App\Models\Company::getMainCompany()->id;
        $company = Company::findOrFail($companyId);
        
        return view('series.create', compact('company'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'tipo_documento' => 'required|in:01,03,NV',
            'serie' => 'required|max:4|min:2|regex:/^[A-Z]{1,2}\d{2,3}$/',
            'numero_inicio' => 'required|integer|min:1',
        ], [
            'serie.required' => 'La serie es requerida',
            'serie.max' => 'La serie debe tener máximo 4 caracteres',
            'serie.regex' => 'Formato inválido. Use: F001, B001, NV01 (letra(s) + números)',
        ]);

        $existing = Serie::where('company_id', $validated['company_id'])
            ->where('tipo_documento', $validated['tipo_documento'])
            ->where('serie', $validated['serie'])
            ->first();

        if ($existing) {
            return back()->with('error', 'Ya existe una serie con este número y tipo de documento');
        }

        Serie::create([
            'company_id' => $validated['company_id'],
            'tipo_documento' => $validated['tipo_documento'],
            'serie' => strtoupper($validated['serie']),
            'numero_actual' => $validated['numero_inicio'] - 1,
            'estado' => 'ACTIVO',
        ]);

        return redirect()->route('series.index')->with('success', 'Serie creada correctamente');
    }

    public function edit(Serie $serie)
    {
        return view('series.edit', compact('serie'));
    }

    public function update(Request $request, Serie $serie)
    {
        $validated = $request->validate([
            'numero_inicio' => 'required|integer|min:0',
        ]);

        $newStart = $validated['numero_inicio'];
        if ($newStart > $serie->numero_actual + 1) {
            $serie->numero_actual = $newStart - 1;
        } elseif ($newStart <= $serie->numero_actual) {
            $diff = $serie->numero_actual - $newStart + 1;
            $serie->numero_actual = $newStart - 1;
        }

        $serie->save();

        return redirect()->route('series.index')->with('success', 'Serie actualizada correctamente');
    }

    public function destroy(Serie $serie)
    {
        $serie->update(['estado' => 'INACTIVO']);
        return back()->with('success', 'Serie eliminada');
    }
}