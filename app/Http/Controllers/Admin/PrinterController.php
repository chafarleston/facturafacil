<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Printer;
use App\Services\PrintServerService;
use Illuminate\Http\Request;

class PrinterController extends Controller
{
    public function index(PrintServerService $printServer)
    {
        $printers = Printer::orderBy('name')->get();
        $serverRunning = $printServer->isServerRunning();
        $availablePrinters = $serverRunning ? $printServer->getAvailablePrinters() : [];

        return view('admin.printers.index', compact('printers', 'serverRunning', 'availablePrinters'));
    }

    public function detect(PrintServerService $printServer)
    {
        if (!$printServer->isServerRunning()) {
            return back()->with('error', 'El servidor de impresión no está disponible');
        }

        $available = $printServer->getAvailablePrinters();
        $existing = Printer::pluck('printer_name')->toArray();
        $count = 0;

        foreach ($available as $p) {
            if (!in_array($p['name'], $existing)) {
                Printer::create([
                    'name' => $p['name'],
                    'type' => 'local',
                    'printer_name' => $p['name'],
                    'active' => true,
                ]);
                $count++;
            }
        }

        return back()->with('success', "{$count} impresora(s) detectada(s) e importadas");
    }

    public function create()
    {
        $assignments = [
            '' => 'Sin asignar',
            'cocina-1' => 'KDS Cocina 1',
            'cocina-2' => 'KDS Cocina 2',
            'bar-1' => 'KDS Bar 1',
            'bar-2' => 'KDS Bar 2',
            'pos' => 'Punto de Venta',
        ];
        return view('admin.printers.create', compact('assignments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'type' => 'required|in:local,network',
            'printer_name' => 'required_if:type,local|nullable|max:255',
            'ip_address' => 'required_if:type,network|nullable|ip',
            'port' => 'required_if:type,network|nullable|integer|min:1|max:65535',
            'assigned_to' => 'nullable|max:50',
            'active' => 'boolean',
        ]);

        Printer::create($validated);

        return redirect()->route('printers.index')
            ->with('success', 'Impresora registrada correctamente');
    }

    public function edit(Printer $printer)
    {
        $assignments = [
            '' => 'Sin asignar',
            'cocina-1' => 'KDS Cocina 1',
            'cocina-2' => 'KDS Cocina 2',
            'bar-1' => 'KDS Bar 1',
            'bar-2' => 'KDS Bar 2',
            'pos' => 'Punto de Venta',
        ];
        return view('admin.printers.edit', compact('printer', 'assignments'));
    }

    public function update(Request $request, Printer $printer)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'type' => 'required|in:local,network',
            'printer_name' => 'required_if:type,local|nullable|max:255',
            'ip_address' => 'required_if:type,network|nullable|ip',
            'port' => 'required_if:type,network|nullable|integer|min:1|max:65535',
            'assigned_to' => 'nullable|max:50',
            'active' => 'boolean',
        ]);

        $printer->update($validated);

        return redirect()->route('printers.index')
            ->with('success', 'Impresora actualizada correctamente');
    }

    public function destroy(Printer $printer)
    {
        $printer->delete();
        return back()->with('success', 'Impresora eliminada');
    }
}
