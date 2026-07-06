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
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = $validated;
        
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            $data['logo'] = $logoPath;
        }

        Company::create($data);

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
            'departamento' => 'nullable',
            'provincia' => 'nullable',
            'distrito' => 'nullable',
            'ubigeo' => 'nullable|max:6',
            'tipo_contribuyente' => 'nullable',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'certificado' => 'nullable|file|max:10240',
            'certificado_password' => 'nullable|string',
            'tax_type' => 'nullable|in:general,restaurant',
            'igv_percent' => 'nullable|numeric|min:0|max:100',
            'reduced_igv_percent' => 'nullable|numeric|min:0|max:100',
            'soap_type_id' => 'nullable|in:01,02',
            'soap_username' => 'nullable|string|max:255',
            'soap_password' => 'nullable|string|max:255',
        ]);

        $data = $validated;

        if ($request->hasFile('logo')) {
            if ($company->logo) {
                \Storage::disk('public')->delete($company->logo);
            }
            $logoPath = $request->file('logo')->store('logos', 'public');
            $data['logo'] = $logoPath;
        }

if ($request->hasFile('certificado')) {
    $certPassword = $request->input('certificado_password');
    if (empty($certPassword)) {
        return back()->withErrors(['certificado_password' => 'Debe ingresar la contraseña del certificado'])->withInput();
    }
    try {
        $certFile = $request->file('certificado');
        $tempPath = $certFile->getRealPath();
        $ext = strtolower($certFile->getClientOriginalExtension());
        $pfxFilename = $company->ruc . '_certificate.' . ($ext === 'p12' ? 'p12' : 'pfx');
        $pemFilename = $company->ruc . '_certificate.pem';

        // Use OpenSSL 1.1.1 CLI to verify password
        $opensslBin = 'C:\laragon\bin\git\mingw64\bin\openssl.exe';
        $verifyCmd = escapeshellarg($opensslBin) . ' pkcs12 -in ' . escapeshellarg($tempPath) . ' -passin pass:' . escapeshellarg($certPassword) . ' -noout 2>&1';
        exec($verifyCmd, $verifyOutput, $verifyExitCode);

        if ($verifyExitCode !== 0) {
            $errorMsg = !empty($verifyOutput) ? implode(' ', $verifyOutput) : 'contraseña inválida';
            \Log::warning('Certificate upload: password verification failed', ['error' => $errorMsg]);
            return back()->withErrors(['certificado_password' => 'La contraseña del certificado no es correcta. ' . $errorMsg])->withInput();
        }
        \Log::info('Certificate password verified via OpenSSL CLI');

        // Store PKCS12 file
        $pfxPath = $certFile->storeAs('certificates', $pfxFilename, 'local');
        $data['certificado_path'] = 'certificates/' . $pfxFilename;
        $data['certificado_password'] = $certPassword;

        // Extract PEM for PHP 8.4 / OpenSSL 3.0 compatibility
        $pemFullPath = storage_path('app/certificates/' . $pemFilename);
        $extractCmd = escapeshellarg($opensslBin) . ' pkcs12 -in ' . escapeshellarg($tempPath) . ' -passin pass:' . escapeshellarg($certPassword) . ' -out ' . escapeshellarg($pemFullPath) . ' -nodes 2>&1';
        exec($extractCmd, $extractOutput, $extractExitCode);

        if ($extractExitCode === 0 && file_exists($pemFullPath)) {
            $data['certificate'] = $pemFilename;
            \Log::info('Certificate PEM extracted for OpenSSL 3.0 compatibility');
        } else {
            \Log::warning('Failed to extract PEM, will use PKCS12 directly', ['error' => implode(' ', $extractOutput)]);
        }
    } catch (\Exception $e) {
        \Log::error('Certificate upload exception: ' . $e->getMessage());
        return back()->withErrors(['certificado' => 'Error al procesar el certificado: ' . $e->getMessage()])->withInput();
    }
}

        $company->update($data);

        return redirect()->route('companies.show', $company)->with('success', 'Empresa actualizada');
    }

    public function updateCertificate(Request $request, Company $company)
    {
        $request->validate([
            'certificado' => 'required|file',
            'certificado_password' => 'required|string',
        ]);

        try {
            $tempPath = $request->file('certificado')->getRealPath();
            $password = $request->certificado_password;
            $ext = strtolower($request->file('certificado')->getClientOriginalExtension());
            $pfxFilename = $company->ruc . '_certificate.' . ($ext === 'p12' ? 'p12' : 'pfx');
            $pemFilename = $company->ruc . '_certificate.pem';

            // Use OpenSSL 1.1.1 CLI to verify password
            $opensslBin = 'C:\laragon\bin\git\mingw64\bin\openssl.exe';
            $verifyCmd = escapeshellarg($opensslBin) . ' pkcs12 -in ' . escapeshellarg($tempPath) . ' -passin pass:' . escapeshellarg($password) . ' -noout 2>&1';
            exec($verifyCmd, $verifyOutput, $verifyExitCode);

            if ($verifyExitCode !== 0) {
                $errorMsg = !empty($verifyOutput) ? implode(' ', $verifyOutput) : 'contraseña inválida';
                \Log::warning('Certificate update: password verification failed', ['error' => $errorMsg]);
                return back()->with('error', 'La clave proporcionada no es correcta. ' . $errorMsg);
            }

            $path = $request->file('certificado')->storeAs('certificates', $pfxFilename);
            if (!$path) {
                return back()->with('error', 'Error al guardar el archivo');
            }

            // Extract PEM for PHP 8.4 / OpenSSL 3.0 compatibility
            $pemFullPath = storage_path('app/certificates/' . $pemFilename);
            $extractCmd = escapeshellarg($opensslBin) . ' pkcs12 -in ' . escapeshellarg($tempPath) . ' -passin pass:' . escapeshellarg($password) . ' -out ' . escapeshellarg($pemFullPath) . ' -nodes 2>&1';
            exec($extractCmd, $extractOutput, $extractExitCode);

            $company->update([
                'certificate' => ($extractExitCode === 0 && file_exists($pemFullPath)) ? $pemFilename : $pfxFilename,
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