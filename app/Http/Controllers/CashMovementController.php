<?php

namespace App\Http\Controllers;

use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashMovementController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('permission', 'manage_cash_movements');

        $companyId = $request->company_id ?? Company::getMainCompany()->id;

        $cajaAbierta = CashRegister::where('company_id', $companyId)
            ->where('estado', 'ABIERTA')
            ->first();

        $movimientos = collect();
        if ($cajaAbierta) {
            $movimientos = CashMovement::with('user')
                ->where('cash_register_id', $cajaAbierta->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('cashmovements.index', compact('cajaAbierta', 'movimientos', 'companyId'));
    }

    public function store(Request $request)
    {
        $this->authorize('permission', 'manage_cash_movements');

        $companyId = Company::getMainCompany()->id;

        $cajaAbierta = CashRegister::where('company_id', $companyId)
            ->where('estado', 'ABIERTA')
            ->first();

        if (!$cajaAbierta) {
            return redirect()->route('cash-movements.index')
                ->with('error', 'No hay caja abierta. Aperture una caja antes de registrar ingresos o gastos.');
        }

        $validated = $request->validate([
            'tipo' => 'required|in:INGRESO,EGRESO',
            'motivo' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0.01',
        ]);

        CashMovement::create([
            'company_id' => $companyId,
            'cash_register_id' => $cajaAbierta->id,
            'user_id' => Auth::id(),
            'tipo' => $validated['tipo'],
            'motivo' => trim($validated['motivo']),
            'monto' => round((float) $validated['monto'], 2),
        ]);

        $this->recalcTotals($cajaAbierta);

        return redirect()->route('cash-movements.index')
            ->with('success', ($validated['tipo'] === 'INGRESO' ? 'Ingreso' : 'Egreso') . ' registrado correctamente.');
    }

    public function destroy(Request $request, CashMovement $cashMovement)
    {
        $this->authorize('permission', 'manage_cash_movements');

        $caja = $cashMovement->cashRegister;

        if (!$caja || !$caja->isOpen()) {
            return redirect()->route('cash-movements.index')
                ->with('error', 'No se puede anular: la caja ya está cerrada.');
        }

        $cashMovement->delete();
        $this->recalcTotals($caja);

        return redirect()->route('cash-movements.index')
            ->with('success', 'Movimiento anulado correctamente.');
    }

    private function recalcTotals(CashRegister $caja): void
    {
        $caja->total_ingresos = round((float) $caja->cashMovements()
            ->where('tipo', 'INGRESO')->sum('monto'), 2);
        $caja->total_egresos = round((float) $caja->cashMovements()
            ->where('tipo', 'EGRESO')->sum('monto'), 2);
        $caja->save();
    }
}
