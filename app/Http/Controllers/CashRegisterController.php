<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashRegisterController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->get('company_id', Auth::user()->company_id);
        
        $cajaAbierta = CashRegister::where('company_id', $companyId)
            ->where('estado', 'ABIERTA')
            ->where('user_id', Auth::id())
            ->first();
            
        $cajas = CashRegister::where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('cashregisters.index', compact('cajas', 'cajaAbierta', 'companyId'));
    }

    public function open(Request $request)
    {
        $request->validate([
            'monto_apertura' => 'required|numeric|min:0'
        ]);

        $companyId = $request->get('company_id', Auth::user()->company_id);
        
        $cajaExistente = CashRegister::where('company_id', $companyId)
            ->where('estado', 'ABIERTA')
            ->first();
            
        if ($cajaExistente) {
            return back()->with('error', 'Ya hay una caja abierta');
        }

        CashRegister::create([
            'company_id' => $companyId,
            'user_id' => Auth::id(),
            'monto_apertura' => $request->monto_apertura,
            'estado' => 'ABIERTA',
        ]);

        return redirect()->route('cashregisters.index')
            ->with('success', 'Caja abierta correctamente');
    }

    public function close(Request $request)
    {
        $caja = CashRegister::findOrFail($request->cashregister_id);
        
        if ($caja->estado === 'CERRADA') {
            return back()->with('error', 'La caja ya está cerrada');
        }

        $companyId = $caja->company_id;
        
        $ventas = Invoice::where('company_id', $companyId)
            ->whereBetween('fecha_emision', [
                \Carbon\Carbon::parse($caja->fecha_apertura)->format('Y-m-d'), 
                now()->format('Y-m-d')
            ])
            ->where('sunat_estado', '!=', 'ANULADO')
            ->get();

        $efectivo = 0;
        $tarjeta = 0;
        $yape = 0;
        $plin = 0;
        $otro = 0;
        
        $facturas = 0;
        $facturasTotal = 0;
        $boletas = 0;
        $boletasTotal = 0;
        $nvs = 0;
        $nvsTotal = 0;

        foreach ($ventas as $v) {
            $metodo = $v->metodo_pago ?? 'EFECTIVO';
            
            if ($metodo === 'EFECTIVO') $efectivo += $v->total;
            elseif ($metodo === 'TARJETA') $tarjeta += $v->total;
            elseif ($metodo === 'YAPE') $yape += $v->total;
            elseif ($metodo === 'PLIN') $plin += $v->total;
            else $otro += $v->total;

            if ($v->tipo_documento === '01') {
                $facturas++;
                $facturasTotal += $v->total;
            } elseif ($v->tipo_documento === '03') {
                $boletas++;
                $boletasTotal += $v->total;
            } else {
                $nvs++;
                $nvsTotal += $v->total;
            }
        }

        $caja->update([
            'ventas_efectivo' => round($efectivo, 2),
            'ventas_tarjeta' => round($tarjeta, 2),
            'ventas_yape' => round($yape, 2),
            'ventas_plin' => round($plin, 2),
            'ventas_otro' => round($otro, 2),
            'cantidad_ventas' => $ventas->count(),
            'total_ventas' => round($efectivo + $tarjeta + $yape + $plin + $otro, 2),
            'monto_cierre' => $request->monto_cierre,
            'estado' => 'CERRADA',
            'fecha_cierre' => now(),
            'observaciones' => $request->observaciones,
        ]);

        return redirect()->route('cashregisters.show', $caja)
            ->with('success', 'Caja cerrada. Resumen generado.');
    }

    public function show(CashRegister $cashregister)
    {
        $ventas = Invoice::where('company_id', $cashregister->company_id)
            ->whereBetween('fecha_emision', [
                \Carbon\Carbon::parse($cashregister->fecha_apertura)->format('Y-m-d'),
                $cashregister->fecha_cierre ? \Carbon\Carbon::parse($cashregister->fecha_cierre)->format('Y-m-d') : now()->format('Y-m-d')
            ])
            ->where('sunat_estado', '!=', 'ANULADO')
            ->get();

        $facturas = $ventas->where('tipo_documento', '01');
        $boletas = $ventas->where('tipo_documento', '03');
        $nvs = $ventas->where('tipo_documento', 'NV');

        return view('cashregisters.show', compact('cashregister', 'facturas', 'boletas', 'nvs'));
    }

    public function pdf(CashRegister $cashregister)
    {
        $ventas = Invoice::where('company_id', $cashregister->company_id)
            ->whereBetween('fecha_emision', [
                \Carbon\Carbon::parse($cashregister->fecha_apertura)->format('Y-m-d'),
                $cashregister->fecha_cierre 
                    ? \Carbon\Carbon::parse($cashregister->fecha_cierre)->format('Y-m-d') 
                    : now()->format('Y-m-d')
            ])
            ->where('sunat_estado', '!=', 'ANULADO')
            ->get();

        $facturas = $ventas->where('tipo_documento', '01');
        $boletas = $ventas->where('tipo_documento', '03');
        $nvs = $ventas->where('tipo_documento', 'NV');

        $html = view('cashregisters.pdf', compact('cashregister', 'facturas', 'boletas', 'nvs'))->render();

        $pdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 10,
            'margin_bottom' => 10,
        ]);

        $pdf->WriteHTML($html);

        return $pdf->Output('resumen-caja-' . $cashregister->id . '.pdf', 'D');
    }

    public function ticketPdf(CashRegister $cashregister)
    {
        $ventas = Invoice::where('company_id', $cashregister->company_id)
            ->whereBetween('fecha_emision', [
                \Carbon\Carbon::parse($cashregister->fecha_apertura)->format('Y-m-d'),
                $cashregister->fecha_cierre ? \Carbon\Carbon::parse($cashregister->fecha_cierre)->format('Y-m-d') : now()->format('Y-m-d')
            ])
            ->where('sunat_estado', '!=', 'ANULADO')
            ->get();

        $facturas = $ventas->where('tipo_documento', '01');
        $boletas = $ventas->where('tipo_documento', '03');
        $nvs = $ventas->where('tipo_documento', 'NV');

        $html = view('cashregisters.ticket', compact('cashregister', 'facturas', 'boletas', 'nvs'))->render();

        $pdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => [80, 200],
            'margin_top' => 2,
            'margin_bottom' => 2,
        ]);

        $pdf->WriteHTML($html);

        return $pdf->Output('resumen-caja-ticket-' . $cashregister->id . '.pdf', 'D');
    }
}