<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Invoice;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use App\Services\PrintService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashRegisterController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->get('company_id', Auth::user()->company_id);
        
        $cajaAbierta = CashRegister::where('company_id', $companyId)
            ->where('estado', 'ABIERTA')
            ->first();
            
        $cajas = CashRegister::where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('cashregisters.index', compact('cajas', 'cajaAbierta', 'companyId'));
    }

    public function open(Request $request)
    {
        $this->authorize('permission', 'open_cashregister');

        $request->validate([
            'monto_apertura' => 'required|numeric|min:0'
        ]);

        $companyId = $request->get('company_id', Auth::user()->company_id);
        if (!$companyId) {
            $mainCompany = \App\Models\Company::where('estado', 'ACTIVO')->orWhere('estado', 1)->first();
            $companyId = $mainCompany ? $mainCompany->id : \App\Models\Company::first()->id;
        }
        
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
            'fecha_apertura' => now(),
            'estado' => 'ABIERTA',
            'referencia' => $request->referencia,
        ]);

        return redirect()->route('cashregisters.index')
            ->with('success', 'Caja abierta correctamente');
    }

    public function close(Request $request)
    {
        $this->authorize('permission', 'close_cashregister');

        $request->validate([
            'cashregister_id' => 'required|exists:cashregisters,id',
            'monto_cierre' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $caja = CashRegister::findOrFail($request->cashregister_id);
        
        if ($caja->estado === 'CERRADA') {
            return back()->with('error', 'La caja ya está cerrada');
        }

        $companyId = $caja->company_id;

        $openTables = RestaurantOrder::where('company_id', $companyId)
            ->whereNotIn('status', ['COMPLETED', 'CANCELLED'])
            ->count();

        if ($openTables > 0) {
            return back()->with('error', "No se puede cerrar caja: hay {$openTables} mesa(s) con pedidos abiertos. Cierre o facture todos los pedidos antes de cerrar caja.");
        }

        $fechaApertura = $caja->fecha_apertura instanceof \Carbon\Carbon
            ? $caja->fecha_apertura 
            : \Carbon\Carbon::parse($caja->fecha_apertura);
        $fechaCierre = now();
        
        $ventas = Invoice::where('company_id', $companyId)
            ->whereRaw("CONCAT(fecha_emision, ' ', COALESCE(hora_emision, '00:00:00')) BETWEEN ? AND ?", [
                $fechaApertura->format('Y-m-d H:i:s'),
                $fechaCierre->format('Y-m-d H:i:s')
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

            if (str_contains($metodo, ' + ')) {
                $parts = explode(' + ', $metodo);
                foreach ($parts as $part) {
                    $part = trim($part);
                    if (str_contains($part, '/')) {
                        [$met, $amt] = explode('/', $part);
                        $amt = (float) $amt;
                    } else {
                        $met = $part;
                        $amt = $v->total / count($parts);
                    }
                    match ($met) {
                        'EFECTIVO' => $efectivo += $amt,
                        'TARJETA' => $tarjeta += $amt,
                        'YAPE' => $yape += $amt,
                        'PLIN' => $plin += $amt,
                        default => $otro += $amt,
                    };
                }
            } else {
                if (str_contains($metodo, '/')) {
                    [$met, $amt] = explode('/', $metodo);
                    $amt = (float) $amt;
                } else {
                    $met = $metodo;
                    $amt = $v->total;
                }
                match ($met) {
                    'EFECTIVO' => $efectivo += $amt,
                    'TARJETA' => $tarjeta += $amt,
                    'YAPE' => $yape += $amt,
                    'PLIN' => $plin += $amt,
                    default => $otro += $amt,
                };
            }

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
            'fecha_cierre' => $fechaCierre,
            'observaciones' => $request->observaciones,
        ]);

        return redirect()->route('cashregisters.show', $caja)
            ->with('success', 'Caja cerrada. Resumen generado.');
    }

    public function show(CashRegister $cashregister)
    {
        if (!$cashregister->fecha_apertura) {
            $cashregister->fecha_apertura = now();
            $cashregister->save();
        }
        if (!$cashregister->fecha_cierre) {
            $cashregister->fecha_cierre = now();
        }
        
        $fechaApertura = $cashregister->fecha_apertura instanceof \Carbon\Carbon 
            ? $cashregister->fecha_apertura 
            : \Carbon\Carbon::parse($cashregister->fecha_apertura);
        $fechaCierre = $cashregister->fecha_cierre instanceof \Carbon\Carbon 
            ? $cashregister->fecha_cierre 
            : \Carbon\Carbon::parse($cashregister->fecha_cierre);
        
        $ventas = Invoice::where('company_id', $cashregister->company_id)
            ->where('fecha_emision', '>=', $fechaApertura->format('Y-m-d'))
            ->where('fecha_emision', '<=', $fechaCierre->format('Y-m-d'))
            ->where('sunat_estado', '!=', 'ANULADO')
            ->with(['items.product.category', 'customer'])
            ->get();

        $ventas = $ventas->filter(function($venta) use ($fechaApertura, $fechaCierre) {
            $horaVenta = !empty($venta->hora_emision) ? $venta->hora_emision : '00:00:00';
            $fechaVenta = \Carbon\Carbon::parse($venta->fecha_emision . ' ' . $horaVenta);
            return $fechaVenta->gte($fechaApertura) && $fechaVenta->lte($fechaCierre);
        });

        $facturas = $ventas->where('tipo_documento', '01');
        $boletas = $ventas->where('tipo_documento', '03');
        $nvs = $ventas->where('tipo_documento', 'NV');

        $categoriasVentas = [];
        $productosVendidos = [];
        $ventasEfectivo = 0;
        $ventasTarjeta = 0;
        $ventasYape = 0;
        $ventasPlin = 0;
        $ventasOtro = 0;
        
        foreach ($ventas as $venta) {
            $metodo = $venta->metodo_pago ?? 'EFECTIVO';

            if (str_contains($metodo, ' + ')) {
                $parts = explode(' + ', $metodo);
                foreach ($parts as $part) {
                    $part = trim($part);
                    if (str_contains($part, '/')) {
                        [$met, $amt] = explode('/', $part);
                        $amt = (float) $amt;
                    } else {
                        $met = $part;
                        $amt = $venta->total / count($parts);
                    }
                    match ($met) {
                        'EFECTIVO' => $ventasEfectivo += $amt,
                        'TARJETA' => $ventasTarjeta += $amt,
                        'YAPE' => $ventasYape += $amt,
                        'PLIN' => $ventasPlin += $amt,
                        default => $ventasOtro += $amt,
                    };
                }
            } else {
                if (str_contains($metodo, '/')) {
                    [$met, $amt] = explode('/', $metodo);
                    $amt = (float) $amt;
                } else {
                    $met = $metodo;
                    $amt = $venta->total;
                }
                match ($met) {
                    'EFECTIVO' => $ventasEfectivo += $amt,
                    'TARJETA' => $ventasTarjeta += $amt,
                    'YAPE' => $ventasYape += $amt,
                    'PLIN' => $ventasPlin += $amt,
                    default => $ventasOtro += $amt,
                };
            }
            
            foreach ($venta->items as $item) {
                $categoriaNombre = $item->product && $item->product->category 
                    ? $item->product->category->nombre 
                    : 'Sin Categoría';
                
                if (!isset($categoriasVentas[$categoriaNombre])) {
                    $categoriasVentas[$categoriaNombre] = ['cantidad' => 0, 'total' => 0];
                }
                $categoriasVentas[$categoriaNombre]['cantidad']++;
                $categoriasVentas[$categoriaNombre]['total'] += $item->precio_venta;
                
                $productoNombre = $item->descripcion;
                if (!isset($productosVendidos[$productoNombre])) {
                    $productosVendidos[$productoNombre] = ['cantidad' => 0, 'total' => 0];
                }
                $productosVendidos[$productoNombre]['cantidad'] += $item->cantidad;
                $productosVendidos[$productoNombre]['total'] += $item->precio_venta;
            }
        }
        
        $totalMetodos = $ventasEfectivo + $ventasTarjeta + $ventasYape + $ventasPlin + $ventasOtro;
        
        arsort($categoriasVentas);
        arsort($productosVendidos);

        $lineasEliminadas = RestaurantOrderItem::with('cancelledBy')
            ->where('kitchen_status', 'CANCELLED')
            ->whereNotNull('cancelled_from')
            ->whereIn('cancelled_from', ['SENT', 'READY', 'DELIVERED'])
            ->where('cancelled_at', '>=', $fechaApertura)
            ->where('cancelled_at', '<=', $fechaCierre)
            ->get();

        return view('cashregisters.show', compact('cashregister', 'facturas', 'boletas', 'nvs', 'ventas', 'categoriasVentas', 'productosVendidos', 'ventasEfectivo', 'ventasTarjeta', 'ventasYape', 'ventasPlin', 'ventasOtro', 'totalMetodos', 'lineasEliminadas'));
    }

    private function getCashRegisterData(CashRegister $cashregister)
    {
        if (!$cashregister->fecha_apertura) {
            $cashregister->fecha_apertura = now();
            $cashregister->save();
        }
        if (!$cashregister->fecha_cierre) {
            $cashregister->fecha_cierre = now();
        }

        $fechaApertura = $cashregister->fecha_apertura instanceof \Carbon\Carbon 
            ? $cashregister->fecha_apertura 
            : \Carbon\Carbon::parse($cashregister->fecha_apertura);
        $fechaCierre = $cashregister->fecha_cierre instanceof \Carbon\Carbon 
            ? $cashregister->fecha_cierre 
            : \Carbon\Carbon::parse($cashregister->fecha_cierre);

        $ventas = Invoice::where('company_id', $cashregister->company_id)
            ->whereRaw("CONCAT(fecha_emision, ' ', COALESCE(hora_emision, '00:00:00')) BETWEEN ? AND ?", [
                $fechaApertura->format('Y-m-d H:i:s'),
                $fechaCierre->format('Y-m-d H:i:s')
            ])
            ->where('sunat_estado', '!=', 'ANULADO')
            ->with(['items.product.category', 'customer'])
            ->get();

        $lineasEliminadas = RestaurantOrderItem::with('cancelledBy')
            ->where('kitchen_status', 'CANCELLED')
            ->whereNotNull('cancelled_from')
            ->whereIn('cancelled_from', ['SENT', 'READY', 'DELIVERED'])
            ->where('cancelled_at', '>=', $fechaApertura)
            ->where('cancelled_at', '<=', $fechaCierre)
            ->get();

        $facturas = $ventas->where('tipo_documento', '01');
        $boletas = $ventas->where('tipo_documento', '03');
        $nvs = $ventas->where('tipo_documento', 'NV');

        $ventasPorMetodo = [];
        $categoriasVentas = [];
        $productosVendidos = [];

        foreach ($ventas as $venta) {
            $metodo = $venta->metodo_pago ?? 'Efectivo';

            if (str_contains($metodo, ' + ')) {
                $parts = explode(' + ', $metodo);
                foreach ($parts as $part) {
                    $part = trim($part);
                    $met = str_contains($part, '/') ? explode('/', $part)[0] : $part;
                    if (!isset($ventasPorMetodo[$met])) {
                        $ventasPorMetodo[$met] = [];
                    }
                    $ventasPorMetodo[$met][] = $venta;
                }
            } else {
                $met = str_contains($metodo, '/') ? explode('/', $metodo)[0] : $metodo;
                if (!isset($ventasPorMetodo[$met])) {
                    $ventasPorMetodo[$met] = [];
                }
                $ventasPorMetodo[$met][] = $venta;
            }

            foreach ($venta->items as $item) {
                $categoriaNombre = $item->product && $item->product->category 
                    ? $item->product->category->nombre 
                    : 'Sin Categoría';

                if (!isset($categoriasVentas[$categoriaNombre])) {
                    $categoriasVentas[$categoriaNombre] = ['cantidad' => 0, 'total' => 0];
                }
                $categoriasVentas[$categoriaNombre]['cantidad']++;
                $categoriasVentas[$categoriaNombre]['total'] += $item->precio_venta;

                $productoNombre = $item->descripcion;
                if (!isset($productosVendidos[$productoNombre])) {
                    $productosVendidos[$productoNombre] = ['cantidad' => 0, 'total' => 0];
                }
                $productosVendidos[$productoNombre]['cantidad'] += $item->cantidad;
                $productosVendidos[$productoNombre]['total'] += $item->precio_venta;
            }
        }

        arsort($categoriasVentas);
        arsort($productosVendidos);

        return compact('cashregister', 'facturas', 'boletas', 'nvs', 'ventasPorMetodo', 'categoriasVentas', 'productosVendidos', 'lineasEliminadas', 'ventas');
    }

    public function pdf(CashRegister $cashregister)
    {
        $data = $this->getCashRegisterData($cashregister);

        $pdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 10,
            'margin_bottom' => 15,
        ]);

        $html = view('cashregisters.pdf', $data)->render();
        $pdf->WriteHTML($html);

        return $pdf->Output('resumen-caja-a4-' . $cashregister->id . '.pdf', 'D');
    }

    public function ticketPdf(CashRegister $cashregister)
    {
        $data = $this->getCashRegisterData($cashregister);

        $pdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => [80, 200],
            'margin_top' => 2,
            'margin_bottom' => 2,
        ]);

        $html = view('cashregisters.ticket', $data)->render();
        $pdf->WriteHTML($html);

        return $pdf->Output('resumen-caja-ticket-' . $cashregister->id . '.pdf', 'D');
    }

    public function printCaja(CashRegister $cashregister)
    {
        try {
            $data = $this->getCashRegisterData($cashregister);
            $text = \App\Services\PlainTextTicket::cashRegisterSummary($cashregister, $data, 'escpos');
            $printService = app(PrintService::class);
            $printer = \App\Models\Printer::where('assigned_to', 'caja')->where('active', true)->first();
            if (!$printer) {
                return back()->with('error', 'No hay impresora Caja configurada');
            }
            app(\App\Services\PrintServerService::class)->printText($printer, $text);
            return back()->with('success', 'Resumen enviado a impresora Caja');
        } catch (\Exception $e) {
            \Log::error('Print caja error: ' . $e->getMessage());
            return back()->with('error', 'Error al imprimir: ' . $e->getMessage());
        }
    }
}