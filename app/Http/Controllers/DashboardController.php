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
            'total' => Invoice::where('company_id', $companyId)->where('tipo_documento', '!=', 'NV')->count(),
            'aceptados' => Invoice::where('company_id', $companyId)->where('sunat_estado', 'ACEPTADO')->count(),
            'pendientes' => Invoice::where('company_id', $companyId)->whereIn('sunat_estado', ['PENDIENTE', 'ENVIADO'])->count(),
            'total_ventas' => Invoice::where('company_id', $companyId)->where('tipo_documento', '!=', 'NV')->where('sunat_estado', '!=', 'ANULADO')->sum('total'),
            'facturas' => Invoice::where('company_id', $companyId)->where('tipo_documento', '01')->count(),
            'boletas' => Invoice::where('company_id', $companyId)->where('tipo_documento', '03')->count(),
            'notas_venta' => Invoice::where('company_id', $companyId)->where('tipo_documento', 'NV')->count(),
            'total_productos' => Product::where('estado', 'ACTIVO')->count(),
            'total_clientes' => Customer::where('company_id', $companyId)->where('estado', 'ACTIVO')->count(),
        ];
        
        $ventasPorDia = [];
        for ($i = 6; $i >= 0; $i--) {
            $fecha = Carbon::now()->subDays($i);
            $ventas = Invoice::where('company_id', $companyId)
                ->whereDate('fecha_emision', $fecha)
                ->where('tipo_documento', '!=', 'NV')
                ->where('sunat_estado', '!=', 'ANULADO')
                ->sum('total');
            
            $ventasPorDia[] = [
                'dia' => $fecha->format('d/m'),
                'fecha' => $fecha->format('Y-m-d'),
                'monto' => round($ventas, 2),
            ];
        }
        
        $monthlySales = [];
        for ($i = 29; $i >= 0; $i--) {
            $fecha = Carbon::now()->subDays($i);
            $ventas = Invoice::where('company_id', $companyId)
                ->whereDate('fecha_emision', $fecha)
                ->where('tipo_documento', '!=', 'NV')
                ->where('sunat_estado', '!=', 'ANULADO')
                ->sum('total');
            
            $monthlySales[] = [
                'dia' => $fecha->format('d'),
                'fecha' => $fecha->format('Y-m-d'),
                'monto' => round($ventas, 2),
            ];
        }
        
        $topProducts = \DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('products', 'invoice_items.product_id', '=', 'products.id')
            ->where('invoices.company_id', $companyId)
            ->where('invoices.tipo_documento', '!=', 'NV')
            ->whereMonth('invoices.fecha_emision', Carbon::now()->month)
            ->selectRaw('products.descripcion, SUM(invoice_items.cantidad) as total_vendido, SUM(invoice_items.precio_venta) as total_monto')
            ->groupBy('products.descripcion')
            ->orderBy('total_vendido', 'desc')
            ->limit(5)
            ->get();
        
        $recentInvoices = Invoice::with('customer')
            ->where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        $todaySales = Invoice::where('company_id', $companyId)
            ->whereDate('fecha_emision', Carbon::today())
            ->where('tipo_documento', '!=', 'NV')
            ->where('sunat_estado', '!=', 'ANULADO')
            ->sum('total');
        
        $yesterdaySales = Invoice::where('company_id', $companyId)
            ->whereDate('fecha_emision', Carbon::yesterday())
            ->where('tipo_documento', '!=', 'NV')
            ->where('sunat_estado', '!=', 'ANULADO')
            ->sum('total');
        
        $stats['ventas_hoy'] = $todaySales;
        $stats['ventas_ayer'] = $yesterdaySales;
        $stats['crecimiento'] = $yesterdaySales > 0 ? (($todaySales - $yesterdaySales) / $yesterdaySales) * 100 : ($todaySales > 0 ? 100 : 0);
        
        return view('dashboard', compact('stats', 'ventasPorDia', 'recentInvoices', 'topProducts', 'monthlySales'));
    }
}