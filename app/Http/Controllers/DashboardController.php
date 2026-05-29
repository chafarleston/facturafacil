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
            'notas_venta' => Invoice::where('company_id', $companyId)->where('tipo_documento', 'NV')->count(),
            'total_productos' => Product::where('estado', 'ACTIVO')->count(),
            'total_clientes' => Customer::where('company_id', $companyId)->where('estado', 'ACTIVO')->count(),
        ];
        
        $ventasPorDia = [];
        for ($i = 6; $i >= 0; $i--) {
            $fecha = Carbon::now()->subDays($i);
            $ventas = Invoice::where('company_id', $companyId)
                ->whereDate('fecha_emision', $fecha)
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

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $startOfPrevMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfPrevMonth = Carbon::now()->subMonth()->endOfMonth();

        $currentMonthSales = Invoice::where('company_id', $companyId)
            ->whereBetween('fecha_emision', [$startOfMonth, $endOfMonth])
            ->where('sunat_estado', '!=', 'ANULADO')
            ->sum('total');

        $prevMonthSales = Invoice::where('company_id', $companyId)
            ->whereBetween('fecha_emision', [$startOfPrevMonth, $endOfPrevMonth])
            ->where('sunat_estado', '!=', 'ANULADO')
            ->sum('total');

        $stats['ventas_mes'] = $currentMonthSales;
        $stats['ventas_mes_anterior'] = $prevMonthSales;
        $stats['crecimiento'] = $prevMonthSales > 0 ? (($currentMonthSales - $prevMonthSales) / $prevMonthSales) * 100 : ($currentMonthSales > 0 ? 100 : 0);

        $stats['total'] = Invoice::where('company_id', $companyId)
            ->whereBetween('fecha_emision', [$startOfMonth, $endOfMonth])
            ->count();

        $stats['aceptados'] = Invoice::where('company_id', $companyId)
            ->whereBetween('fecha_emision', [$startOfMonth, $endOfMonth])
            ->where('sunat_estado', 'ACEPTADO')
            ->count();

        $stats['pendientes'] = Invoice::where('company_id', $companyId)
            ->whereBetween('fecha_emision', [$startOfMonth, $endOfMonth])
            ->whereIn('sunat_estado', ['PENDIENTE', 'ENVIADO'])
            ->count();

        $stats['facturas'] = Invoice::where('company_id', $companyId)
            ->whereBetween('fecha_emision', [$startOfMonth, $endOfMonth])
            ->where('tipo_documento', '01')
            ->count();

        $stats['boletas'] = Invoice::where('company_id', $companyId)
            ->whereBetween('fecha_emision', [$startOfMonth, $endOfMonth])
            ->where('tipo_documento', '03')
            ->count();

        $stats['notas_venta'] = Invoice::where('company_id', $companyId)
            ->whereBetween('fecha_emision', [$startOfMonth, $endOfMonth])
            ->where('tipo_documento', 'NV')
            ->count();

        return view('dashboard', compact('stats', 'ventasPorDia', 'recentInvoices', 'topProducts', 'monthlySales'));
    }
}