<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Preventa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PreventaController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $this->companyId();
        $tipo = $this->tipo($request->get('tipo', Preventa::TIPO_COTIZACION));
        $search = trim((string) $request->get('search', ''));

        $preventas = Preventa::where('company_id', $companyId)
            ->where('tipo', $tipo)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subquery) use ($search) {
                    $subquery->where('numero', 'like', "%{$search}%")
                        ->orWhere('cliente_nombre', 'like', "%{$search}%")
                        ->orWhere('titulo', 'like', "%{$search}%")
                        ->orWhere('descripcion', 'like', "%{$search}%");
                });
            })
            ->latest('fecha')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $conteos = Preventa::where('company_id', $companyId)
            ->selectRaw('tipo, count(*) as total')
            ->groupBy('tipo')
            ->pluck('total', 'tipo');

        return view('preventas.index', [
            'preventas' => $preventas,
            'tipos' => Preventa::TIPOS,
            'estados' => Preventa::ESTADOS,
            'tipo' => $tipo,
            'conteos' => $conteos,
            'search' => $search,
        ]);
    }

    public function create(Request $request)
    {
        $tipo = $this->tipo($request->get('tipo', Preventa::TIPO_COTIZACION));

        return view('preventas.create', [
            'tipo' => $tipo,
            'tipos' => Preventa::TIPOS,
            'estados' => Preventa::ESTADOS,
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $this->companyId();
        $tipo = $this->tipo($request->get('tipo', Preventa::TIPO_COTIZACION));
        $data = $this->validatedData($request);

        Preventa::create(array_merge($data, [
            'company_id' => $companyId,
            'user_id' => Auth::id(),
            'tipo' => $tipo,
            'numero' => Preventa::nextNumero($companyId, $tipo),
        ]));

        return redirect()
            ->route('preventas.index', ['tipo' => $tipo])
            ->with('success', 'Registro de ' . strtolower(Preventa::TIPOS[$tipo]) . ' creado correctamente.');
    }

    public function show($id)
    {
        $preventa = $this->find($id);

        return view('preventas.show', [
            'preventa' => $preventa,
            'tipos' => Preventa::TIPOS,
            'estados' => Preventa::ESTADOS,
        ]);
    }

    public function edit($id)
    {
        $preventa = $this->find($id);

        return view('preventas.edit', [
            'preventa' => $preventa,
            'tipo' => $preventa->tipo,
            'tipos' => Preventa::TIPOS,
            'estados' => Preventa::ESTADOS,
        ]);
    }

    public function update(Request $request, $id)
    {
        $preventa = $this->find($id);
        $data = $this->validatedData($request);

        $preventa->update($data);

        return redirect()
            ->route('preventas.index', ['tipo' => $preventa->tipo])
            ->with('success', 'Registro actualizado correctamente.');
    }

    public function destroy($id)
    {
        $preventa = $this->find($id);
        $tipo = $preventa->tipo;
        $preventa->delete();

        return redirect()
            ->route('preventas.index', ['tipo' => $tipo])
            ->with('success', 'Registro eliminado correctamente.');
    }

    private function companyId(): int
    {
        return (int) Company::getMainCompany()->id;
    }

    private function tipo(string $tipo): string
    {
        return array_key_exists($tipo, Preventa::TIPOS) ? $tipo : Preventa::TIPO_COTIZACION;
    }

    private function find($id): Preventa
    {
        return Preventa::where('company_id', $this->companyId())->findOrFail($id);
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'cliente_nombre' => 'required|string|max:255',
            'cliente_documento' => 'nullable|string|max:20',
            'cliente_email' => 'nullable|email|max:255',
            'cliente_telefono' => 'nullable|string|max:30',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:5000',
            'monto' => 'nullable|numeric|min:0|max:99999999.99',
            'estado' => 'required|in:' . implode(',', array_keys(Preventa::ESTADOS)),
            'fecha' => 'required|date',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha',
            'observaciones' => 'nullable|string|max:5000',
        ]);
    }
}
