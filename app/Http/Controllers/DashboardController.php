<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Product;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $companyId = \App\Models\Company::getMainCompany()->id;
        
        $stats = [
            'total' => Invoice::where('company_id', $companyId)->count(),
            'aceptados' => Invoice::where('company_id', $companyId)->where('sunat_estado', 'ACEPTADO')->count(),
            'pendientes' => Invoice::where('company_id', $companyId)->whereIn('sunat_estado', ['PENDIENTE', 'ENVIADO'])->count(),
            'total_ventas' => Invoice::where('company_id', $companyId)->where('sunat_estado', '!=', 'ANULADO')->sum('total'),
            'facturas' => Invoice::where('company_id', $companyId)->where('tipo_documento', '01')->count(),
            'boletas' => Invoice::where('company_id', $companyId)->where('tipo_documento', '03')->count(),
            'notas_credito' => Invoice::where('company_id', $companyId)->where('tipo_documento', '07')->count(),
        ];
        
        $ventasPorDia = [];
        for ($i = 6; $i >= 0; $i--) {
            $fecha = Carbon::now()->subDays($i);
            $ventas = Invoice::where('company_id', $companyId)
                ->whereDate('fecha_emision', $fecha)
                ->where('sunat_estado', '!=', 'ANULADO')
                ->sum('total');
            
            $ventasPorDia[] = [
                'dia' => $fecha->format('d'),
                'monto' => number_format($ventas, 0),
                'porcentaje' => $stats['total_ventas'] > 0 ? ($ventas / $stats['total_ventas']) * 100 : 0
            ];
        }
        
        $maxVenta = collect($ventasPorDia)->max('porcentaje') ?: 1;
        foreach ($ventasPorDia as &$venta) {
            $venta['porcentaje'] = $venta['porcentaje'] > 0 ? ($venta['porcentaje'] / $maxVenta) * 100 : 0;
        }
        
        $recentInvoices = Invoice::with('customer')
            ->where('company_id', $companyId)
            ->orderBy('fecha_emision', 'desc')
            ->limit(10)
            ->get();
        
        return view('dashboard', compact('stats', 'ventasPorDia', 'recentInvoices'));
    }
}