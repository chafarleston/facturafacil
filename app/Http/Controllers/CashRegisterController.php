<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\CashMovement;
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
        $this->authorize('permission', 'view_cashregisters');
        $companyId = \App\Models\Company::getMainCompany()->id;

        $user = auth()->user();
        $canViewHistory = $user && $user->hasPermission('view_cashregister_history');

        $cajaAbierta = CashRegister::where('company_id', $companyId)
            ->where('estado', 'ABIERTA')
            ->first();

        $cajas = $canViewHistory
            ? CashRegister::where('company_id', $companyId)->orderBy('created_at', 'desc')->paginate(15)
            : collect();

        $precuadreEnabled = \App\Models\Setting::isPrecuadreEnabled();

        return view('cashregisters.index', compact('cajas', 'cajaAbierta', 'companyId', 'canViewHistory', 'precuadreEnabled'));
    }

    public function open(Request $request)
    {
        $this->authorize('permission', 'open_cashregister');

        $request->validate([
            'monto_apertura' => 'required|numeric|min:0'
        ]);

        $companyId = \App\Models\Company::getMainCompany()->id;
        
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
        if (!auth()->user() || !auth()->user()->hasPermission('close_cashregister')) {
            return redirect()->route('cashregisters.index')
                ->with('permission_modal', 'Solo el Administrador puede ejecutar estas tareas');
        }

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
            ->where('order_type', '!=', 'kiosko')
            ->count();

        $openKiosko = RestaurantOrder::where('company_id', $companyId)
            ->whereNotIn('status', ['COMPLETED', 'CANCELLED'])
            ->where('order_type', 'kiosko')
            ->count();

        if ($openTables > 0 || $openKiosko > 0) {
            $mensaje = 'No se puede cerrar caja: ';
            $partes = [];
            if ($openTables > 0) {
                $partes[] = "{$openTables} mesa(s) con pedidos abiertos";
            }
            if ($openKiosko > 0) {
                $partes[] = "{$openKiosko} pedido(s) de kiosko pendientes";
            }
            $mensaje .= implode(' y ', $partes) . '. Cierre o facture todos los pedidos antes de cerrar caja.';
            return back()->with('error', $mensaje);
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

        $paymentTotals = $this->paymentTotals($ventas);
        $totalVentas = round($ventas->sum('total'), 2);
        if (abs($totalVentas - $paymentTotals['total']) > 0.01) {
            return back()->with('error', 'No se puede cerrar caja: hay comprobantes con pagos incompletos. Faltante: S/ ' . number_format($totalVentas - $paymentTotals['total'], 2));
        }

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
        $kioskoTotal = 0;
        $kioskoCount = 0;

        foreach ($ventas as $v) {
            if (($v->order_source ?? '') === 'kiosko') {
                $kioskoTotal += $v->total;
                $kioskoCount++;
            }
            $metodo = $v->metodo_pago ?? 'EFECTIVO';

            if (str_contains($metodo, ' + ')) {
                $parts = explode(' + ', $metodo);
                foreach ($parts as $part) {
                    $part = trim($part);
                    if (str_contains($part, '/')) {
                        [$met, $amt] = explode('/', $part);
                        $amt = min((float) $amt, (float) $v->total);
                    } else {
                        $met = $part;
                        $amt = min((float) $v->total / count($parts), (float) $v->total);
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
                    $amt = min((float) $amt, (float) $v->total);
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

        $totalIngresos = round((float) $caja->cashMovements()->where('tipo', 'INGRESO')->sum('monto'), 2);
        $totalEgresos = round((float) $caja->cashMovements()->where('tipo', 'EGRESO')->sum('monto'), 2);

        $caja->update([
            'ventas_efectivo' => round($efectivo, 2),
            'ventas_tarjeta' => round($tarjeta, 2),
            'ventas_yape' => round($yape, 2),
            'ventas_plin' => round($plin, 2),
            'ventas_otro' => round($otro, 2),
            'cantidad_ventas' => $ventas->count(),
            'total_ventas' => round($efectivo + $tarjeta + $yape + $plin + $otro, 2),
            'total_ingresos' => $totalIngresos,
            'total_egresos' => $totalEgresos,
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
        $this->authorize('permission', 'view_cashregisters');
        $data = $this->getCashRegisterData($cashregister);
        extract($data);
        
        $paymentTotals = $this->paymentTotals($ventas);
        $ventasEfectivo = $paymentTotals['efectivo'];
        $ventasTarjeta = $paymentTotals['tarjeta'];
        $ventasYape = $paymentTotals['yape'];
        $ventasPlin = $paymentTotals['plin'];
        $ventasOtro = $paymentTotals['otro'];
        $totalMetodos = $paymentTotals['total'];
        $totalVentas = round($ventas->sum('total'), 2);
        $diferenciaPagos = round($totalVentas - $totalMetodos, 2);
        $comprobantesDescuadrados = $ventas->map(function ($venta) use ($paymentTotals) {
            $pagado = $paymentTotals['porComprobante'][$venta->id] ?? 0;

            return [
                'venta' => $venta,
                'pagado' => $pagado,
                'faltante' => round((float) $venta->total - $pagado, 2),
            ];
        })->filter(fn ($item) => $item['faltante'] > 0.009)->values();

        // Saldo Final de Efectivo = apertura(ingreso) + ventas_efectivo + ingresos - egresos - cierre(egreso)
        $saldoFinalEfectivo = round(
            (float) ($cashregister->monto_apertura ?? 0)
            + (float) $ventasEfectivo
            + (float) ($totalIngresos ?? 0)
            - (float) ($totalEgresos ?? 0)
            - (float) ($cashregister->monto_cierre ?? 0), 2);
        
        $kioskoVentas = $ventas->where('order_source', 'kiosko');
        $kioskoTotal = $kioskoVentas->sum('total');
        $kioskoCount = $kioskoVentas->count();

        return view('cashregisters.show', compact(
            'cashregister', 'facturas', 'boletas', 'nvs', 'ventas',
            'categoriasVentas', 'productosVendidos',
            'ventasEfectivo', 'ventasTarjeta', 'ventasYape', 'ventasPlin', 'ventasOtro',
            'totalMetodos', 'lineasEliminadas', 'kioskoTotal', 'kioskoCount',
            'movimientos', 'totalIngresos', 'totalEgresos', 'saldoFinalEfectivo',
            'totalVentas', 'diferenciaPagos', 'comprobantesDescuadrados'
        ));
    }

    private function paymentTotals($ventas): array
    {
        $totals = [
            'efectivo' => 0.0,
            'tarjeta' => 0.0,
            'yape' => 0.0,
            'plin' => 0.0,
            'otro' => 0.0,
            'porComprobante' => [],
        ];

        foreach ($ventas as $venta) {
            $metodo = trim((string) ($venta->metodo_pago ?? 'EFECTIVO'));
            $parts = str_contains($metodo, ' + ') ? explode(' + ', $metodo) : [$metodo];
            $count = max(1, count($parts));
            $pagado = 0.0;

            foreach ($parts as $part) {
                $part = trim($part);
                if ($part === '') continue;

                if (str_contains($part, '/')) {
                    [$met, $amt] = explode('/', $part, 2);
                    $amt = (float) $amt;
                } else {
                    $met = $part;
                    $amt = (float) $venta->total / $count;
                }

                $amt = min($amt, (float) $venta->total);
                $pagado += $amt;

                $key = match (strtoupper(trim($met))) {
                    'EFECTIVO' => 'efectivo',
                    'TARJETA' => 'tarjeta',
                    'YAPE' => 'yape',
                    'PLIN' => 'plin',
                    default => 'otro',
                };
                $totals[$key] += $amt;
            }

            $totals['porComprobante'][$venta->id] = round($pagado, 2);
        }

        foreach (['efectivo', 'tarjeta', 'yape', 'plin', 'otro'] as $key) {
            $totals[$key] = round($totals[$key], 2);
        }
        $totals['total'] = round(
            $totals['efectivo'] + $totals['tarjeta'] + $totals['yape'] + $totals['plin'] + $totals['otro'],
            2
        );

        return $totals;
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
            ->whereHas('order', fn ($query) => $query->where('company_id', $cashregister->company_id))
            ->where('cancelled_at', '>=', $fechaApertura)
            ->where('cancelled_at', '<=', $fechaCierre)
            ->orderBy('cancelled_at')
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
                if ($item->descripcion === 'POR CONSUMO' && !empty($item->detalle_consumo)) {
                    foreach ($item->detalle_consumo as $detalle) {
                        $nombre = $detalle['product_name'] ?? 'Producto';
                        if (!isset($productosVendidos[$nombre])) {
                            $productosVendidos[$nombre] = ['cantidad' => 0, 'total' => 0];
                        }
                        $productosVendidos[$nombre]['cantidad'] += $detalle['quantity'] ?? 0;
                        $productosVendidos[$nombre]['total'] += $detalle['total'] ?? 0;
                    }
                } else {
                    $productoNombre = $item->descripcion;
                    if (!isset($productosVendidos[$productoNombre])) {
                        $productosVendidos[$productoNombre] = ['cantidad' => 0, 'total' => 0];
                    }
                    $productosVendidos[$productoNombre]['cantidad'] += $item->cantidad;
                    $productosVendidos[$productoNombre]['total'] += $item->precio_venta;
                }
            }
        }

        arsort($categoriasVentas);
        arsort($productosVendidos);

        $movimientos = CashMovement::with('user')
            ->where('cash_register_id', $cashregister->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalIngresos = $cashregister->total_ingresos ?? 0;
        $totalEgresos = $cashregister->total_egresos ?? 0;

        // Solo el EFECTIVO es dinero en caja (Yape/Plin/Tarjeta son pagos virtuales)
        $ventasEfectivo = 0;
        foreach ($ventas as $venta) {
            $pago = $venta->metodo_pago ?? 'EFECTIVO';
            if (str_contains($pago, ' + ')) {
                $parts = explode(' + ', $pago);
                foreach ($parts as $part) {
                    $part = trim($part);
                    $amt = str_contains($part, '/')
                        ? min((float) explode('/', $part)[1], (float) $venta->total)
                        : round((float) $venta->total / count($parts), 2);
                    if (str_starts_with(strtoupper($part), 'EFECT')) {
                        $ventasEfectivo += $amt;
                    }
                }
            } else {
                $part = $pago;
                if (str_starts_with(strtoupper(explode('/', $part)[0]), 'EFECT')) {
                    $ventasEfectivo += str_contains($part, '/')
                        ? min((float) explode('/', $part)[1], (float) $venta->total)
                        : (float) $venta->total;
                }
            }
        }
        $ventasEfectivo = round($ventasEfectivo, 2);

        // Saldo Final de Efectivo = apertura(ingreso) + ventas_efectivo + ingresos - egresos - cierre(egreso)
        // Yape/Plin/Tarjeta son pagos virtuales: no cuentan, solo informativo.
        // Positivo = sobra efectivo · Negativo = faltante (caja negativa)
        $saldoFinalEfectivo = round(
            (float) ($cashregister->monto_apertura ?? 0)
            + (float) $ventasEfectivo
            + (float) $totalIngresos
            - (float) $totalEgresos
            - (float) ($cashregister->monto_cierre ?? 0), 2);

        // Config de reporte (si no existe, se tratan como "todo visible" en las vistas)
        $reportConfig = \App\Models\CashReportSetting::where('company_id', $cashregister->company_id)->first();

        $paymentTotals = $this->paymentTotals($ventas);
        $cashregister->setAttribute('ventas_efectivo', $paymentTotals['efectivo']);
        $cashregister->setAttribute('ventas_tarjeta', $paymentTotals['tarjeta']);
        $cashregister->setAttribute('ventas_yape', $paymentTotals['yape']);
        $cashregister->setAttribute('ventas_plin', $paymentTotals['plin']);
        $cashregister->setAttribute('ventas_otro', $paymentTotals['otro']);
        $cashregister->setAttribute('total_ventas', round($ventas->sum('total'), 2));
        $cashregister->setAttribute('total_pagos', $paymentTotals['total']);
        $cashregister->setAttribute('diferencia_pagos', round($ventas->sum('total') - $paymentTotals['total'], 2));

        return compact('cashregister', 'facturas', 'boletas', 'nvs', 'ventasPorMetodo', 'categoriasVentas', 'productosVendidos', 'lineasEliminadas', 'ventas', 'movimientos', 'totalIngresos', 'totalEgresos', 'saldoFinalEfectivo', 'ventasEfectivo', 'reportConfig');
    }

    public function pdf(CashRegister $cashregister)
    {
        $this->authorize('permission', 'view_cashregisters');
        $data = $this->getCashRegisterData($cashregister);

        $pdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 10,
            'margin_bottom' => 15,
        ]);

        $html = view('cashregisters.pdf', $data)->render();
        $pdf->WriteHTML($html);

        $filename = 'resumen-caja-a4-' . $cashregister->id . '.pdf';
        return response($pdf->Output($filename, 'S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function ticketPdf(CashRegister $cashregister)
    {
        $this->authorize('permission', 'view_cashregisters');
        $data = $this->getCashRegisterData($cashregister);

        $pdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => [80, 200],
            'margin_top' => 2,
            'margin_bottom' => 2,
        ]);

        $html = view('cashregisters.ticket', $data)->render();
        $pdf->WriteHTML($html);

        $filename = 'resumen-caja-ticket-' . $cashregister->id . '.pdf';
        return response($pdf->Output($filename, 'S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function printCaja(CashRegister $cashregister)
    {
        $this->authorize('permission', 'view_cashregisters');
        try {
            $data = $this->preparePrintData($cashregister);

            $printer = \App\Models\Printer::where('assigned_to', 'caja')->where('active', true)->first();
            if (!$printer) {
                return back()->with('error', 'No hay impresora Caja configurada');
            }
            $width = \App\Services\PlainTextTicket::widthForPaper($printer->paper_size);
            $text = \App\Services\PlainTextTicket::cashRegisterSummary($cashregister, $data, 'escpos', $width, 'CIERRE DE CAJA');
            app(\App\Services\PrintServerService::class)->printText($printer, $text);
            return back()->with('success', 'Resumen enviado a impresora Caja');
        } catch (\Exception $e) {
            \Log::error('Print caja error: ' . $e->getMessage());
            return back()->with('error', 'Error al imprimir: ' . $e->getMessage());
        }
    }

    public function printPrecuadre(CashRegister $cashregister)
    {
        $this->authorize('permission', 'view_cashregisters');

        if ($cashregister->estado === 'CERRADA') {
            return back()->with('error', 'La caja ya está cerrada');
        }

        try {
            $data = $this->preparePrintData($cashregister);

            $printer = \App\Models\Printer::where('assigned_to', 'caja')->where('active', true)->first();
            if (!$printer) {
                return back()->with('error', 'No hay impresora Caja configurada');
            }
            $width = \App\Services\PlainTextTicket::widthForPaper($printer->paper_size);
            $text = \App\Services\PlainTextTicket::cashRegisterSummary($cashregister, $data, 'escpos', $width, 'PRECUADRE');
            app(\App\Services\PrintServerService::class)->printText($printer, $text);
            return back()->with('success', 'Precuadre enviado a impresora Caja');
        } catch (\Exception $e) {
            \Log::error('Print precuadre error: ' . $e->getMessage());
            return back()->with('error', 'Error al imprimir: ' . $e->getMessage());
        }
    }

    private function preparePrintData(CashRegister $cashregister): array
    {
        $data = $this->getCashRegisterData($cashregister);

        $ventas = $data['ventas'];
        $paymentTotals = $this->paymentTotals($ventas);
        $data['total_ventas'] = round($ventas->sum('total'), 2);
        $data['efectivo'] = $paymentTotals['efectivo'];
        $data['tarjeta'] = $paymentTotals['tarjeta'];
        $data['yape'] = $paymentTotals['yape'];
        $data['plin'] = $paymentTotals['plin'];
        $data['otro'] = $paymentTotals['otro'];
        $data['total_pagos'] = $paymentTotals['total'];
        $data['diferenciaPagos'] = round($data['total_ventas'] - $paymentTotals['total'], 2);

        return $data;
    }
}