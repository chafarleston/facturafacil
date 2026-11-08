<?php

namespace App\Http\Controllers;

use App\Services\RestaurantOrderHealthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderHealthController extends Controller
{
    public function __construct(private RestaurantOrderHealthService $health)
    {
    }

    public function index()
    {
        $this->authorize('permission', 'authorize_cancel_orders');

        return view('order-health.index', [
            'health' => $this->health->scan(),
        ]);
    }

    public function cleanup(Request $request)
    {
        $this->authorize('permission', 'authorize_cancel_orders');

        $validated = $request->validate([
            'mode' => 'required|in:safe,completed',
            'orders' => 'nullable|array|max:500',
            'orders.*' => 'integer',
        ]);

        if ($validated['mode'] === 'completed' && empty($validated['orders'])) {
            return back()->with('error', 'Seleccione al menos un pedido para eliminar.');
        }

        try {
            $backup = $this->health->backup();

            if ($validated['mode'] === 'safe') {
                $result = $this->health->cleanupSafe();
                $message = "Limpieza segura: {$result['orders']} pedidos vacios, {$result['items']} items y {$result['batches']} lotes eliminados.";
            } else {
                $result = $this->health->cleanupCompleted($validated['orders']);
                $message = "Pedidos eliminados: {$result['deleted']}.";
                if (!empty($result['skipped'])) {
                    $message .= ' Omitidos por seguridad: ' . implode(', ', $result['skipped']) . '.';
                }
            }

            Log::info('Limpieza de salud de pedidos', [
                'user_id' => auth()->id(),
                'mode' => $validated['mode'],
                'result' => $result,
                'backup' => $backup,
            ]);

            return redirect()->route('order-health.index')
                ->with('success', $message)
                ->with('health_backup', $backup);
        } catch (\Throwable $e) {
            Log::error('Error en limpieza de salud de pedidos', [
                'user_id' => auth()->id(),
                'mode' => $validated['mode'],
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'No se modifico nada: ' . $e->getMessage());
        }
    }
}
