<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class PosSettingController extends Controller
{
    private function authorizeSettings(): void
    {
        $user = auth()->user();
        abort_unless($user && ($user->isAdmin() || $user->isSuperAdmin() || $user->isCashier()), 403);
    }

    public function edit()
    {
        $this->authorizeSettings();

        return view('possettings.edit', [
            'showProductImages' => Setting::showProductImages('pos'),
            'showCategoryImages' => Setting::showCategoryImages('pos'),
            'defaultDocumentType' => Setting::pos('default_document_type', '03'),
            'defaultPaymentMethod' => Setting::pos('default_payment_method', 'EFECTIVO'),
            'openDrawerCash' => Setting::pos('open_drawer_cash', '1') === '1',
        ]);
    }

    public function update(Request $request)
    {
        $this->authorizeSettings();

        $validated = $request->validate([
            'default_document_type' => 'required|in:01,03,NV',
            'default_payment_method' => 'required|in:EFECTIVO,TARJETA,TRANSFERENCIA,YAPE,PLIN,MIXTO',
        ]);

        Setting::setShowProductImages('pos', $request->boolean('img_products_pos'));
        Setting::setShowCategoryImages('pos', $request->boolean('img_categories_pos'));
        Setting::setPos('default_document_type', $validated['default_document_type']);
        Setting::setPos('default_payment_method', $validated['default_payment_method']);
        Setting::setPos('open_drawer_cash', $request->boolean('open_drawer_cash') ? '1' : '0');

        return redirect()->route('pos-settings.edit')
            ->with('success', 'Configuración del Punto de Venta guardada correctamente.');
    }
}
