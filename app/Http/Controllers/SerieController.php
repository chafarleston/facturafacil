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
            'tipo_documento' => 'required|in:01,03',
            'serie' => 'required|max:4|min:1',
        ], [
            'serie.required' => 'La serie es requerida',
            'serie.max' => 'La serie debe tener máximo 4 caracteres',
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
            'numero_actual' => 0,
            'estado' => 'ACTIVO',
        ]);

        return redirect()->route('series.index')->with('success', 'Serie creada correctamente');
    }

    public function destroy(Serie $serie)
    {
        $serie->update(['estado' => 'INACTIVO']);
        return back()->with('success', 'Serie eliminada');
    }
}