<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::where('estado', 'ACTIVO')->orderBy('is_main', 'desc')->get();
        return view('companies.index', compact('companies'));
    }

    public function create()
    {
        return view('companies.create');
    }

public function store(Request $request)
    {
        $validated = $request->validate([
            'ruc' => ['required', 'size:11', 'unique:companies'],
            'razon_social' => 'required',
            'nombre_comercial' => 'nullable',
            'direccion' => 'nullable',
            'telefono' => 'nullable',
            'email' => 'nullable|email',
            'tipo_contribuyente' => 'nullable',
        ]);

        Company::create($validated);

        return redirect()->route('companies.index')->with('success', 'Empresa creada correctamente');
    }

    public function show(Company $company)
    {
        return view('companies.show', compact('company'));
    }

    public function edit(Company $company)
    {
        return view('companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'ruc' => ['required', 'size:11', Rule::unique('companies')->ignore($company->id)],
            'razon_social' => 'required',
            'nombre_comercial' => 'nullable',
            'direccion' => 'nullable',
            'telefono' => 'nullable',
            'email' => 'nullable|email',
            'tipo_contribuyente' => 'nullable',
        ]);

        $company->update($validated);

        return redirect()->route('companies.show', $company)->with('success', 'Empresa actualizada');
    }

    public function updateCertificate(Request $request, Company $company)
    {
        $request->validate([
            'certificado' => 'required|file',
            'certificado_password' => 'required|string',
        ]);

        try {
            $filename = $company->ruc . '_certificate.pfx';
            $tempPath = $request->file('certificado')->getRealPath();
            $password = $request->certificado_password;
            
            $certificateContent = file_get_contents($tempPath);
            
            $certInfo = [];
            $readResult = @openssl_pkcs12_read($certificateContent, $certInfo, $password);
            
            if (!$readResult) {
                return back()->with('error', 'La clave proporcionada no es correcta. Verifique e intente de nuevo.');
            }
            
            $path = $request->file('certificado')->storeAs('certificates', $filename);

            if (!$path) {
                return back()->with('error', 'Error al guardar el archivo');
            }

            $company->update([
                'certificate' => $filename,
                'certificado_path' => $path,
                'certificado_password' => $request->certificado_password,
            ]);

return back()->with('success', 'Certificado actualizado correctamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    
    public function destroy(Company $company)
    {
        if ($company->is_main) {
            return redirect()->route('companies.index')->with('error', 'No se puede eliminar la empresa principal');
        }
        $company->update(['estado' => 'INACTIVO']);
        return redirect()->route('companies.index')->with('success', 'Empresa eliminada correctamente');
    }
    
    public function setMain(Company $company)
    {
        \App\Models\Company::where('is_main', true)->update(['is_main' => false]);
        $company->update(['is_main' => true, 'estado' => 'ACTIVO']);
        return redirect()->route('companies.index')->with('success', 'Empresa establecida como principal');
    }
}