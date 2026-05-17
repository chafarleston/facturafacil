<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Printer;
use App\Models\PrintJob;
use App\Services\PrintServerService;
use Illuminate\Http\Request;

class PrinterController extends Controller
{
    public function index(PrintServerService $printServer)
    {
        $serverRunning = $printServer->isServerRunning();
        $availablePrinters = $serverRunning ? $printServer->getAvailablePrinters() : [];

        $slots = Printer::orderByRaw("FIELD(assigned_to, 'cocina-1','cocina-2','bar-1','precuenta','precuenta2','precuenta3','caja')")->get();

        return view('admin.printers.index', compact('slots', 'serverRunning', 'availablePrinters'));
    }

    public function queue()
    {
        $jobs = PrintJob::orderBy('id', 'desc')->paginate(20);
        return view('admin.print_jobs.index', compact('jobs'));
    }

    public function retry(PrintJob $printJob)
    {
        $printJob->update(['status' => 'pending', 'error_message' => null]);
        try {
            app(\App\Services\PrintService::class)->processQueue();
        } catch (\Exception $e) {
            \Log::error('Queue process error: ' . $e->getMessage());
        }
        return redirect()->route('printers.queue')->with('success', 'Trabajo re-enviado a la cola');
    }

    public function destroy(PrintJob $printJob)
    {
        $printJob->delete();
        return back()->with('success', 'Trabajo eliminado');
    }

    public function detect(PrintServerService $printServer, Request $request)
    {
        if (!$printServer->isServerRunning()) {
            return back()->with('error', 'El servidor de impresión no está disponible');
        }

        $printerName = $request->input('printer_name');
        $slotId = $request->input('slot_id');

        if ($slotId && $printerName) {
            $printer = Printer::findOrFail($slotId);
            $printer->update([
                'printer_name' => $printerName,
                'type' => 'local',
                'active' => true,
            ]);
            return back()->with('success', "Impresora asignada a {$printer->name}");
        }

        return back()->with('success', 'Impresoras detectadas correctamente');
    }

    public function update(Request $request, Printer $printer)
    {
        $validated = $request->validate([
            'printer_name' => 'nullable|max:255',
            'ip_address' => 'nullable|ip',
            'port' => 'nullable|integer|min:1|max:65535',
            'type' => 'required|in:local,network',
            'active' => 'boolean',
        ]);

        $printer->update($validated);
        return redirect()->route('printers.index')->with('success', 'Impresora actualizada');
    }

    public function status(PrintServerService $printServer)
    {
        $running = $printServer->isServerRunning();
        return response()->json([
            'status' => $running ? 'ok' : 'error',
            'running' => $running,
        ]);
    }
}
