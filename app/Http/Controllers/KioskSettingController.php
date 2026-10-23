<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Floor;
use App\Models\RestaurantTable;
use App\Models\Setting;
use Illuminate\Http\Request;

class KioskSettingController extends Controller
{
    public function edit()
    {
        $this->authorize('permission', 'view_restaurant');

        $company = Company::getMainCompany();
        $kioskTable = RestaurantTable::where('company_id', $company->id)
            ->where('is_for_kiosko', true)
            ->first();

        return view('kiosksettings.edit', [
            'kioskTable' => $kioskTable,
            'deliveryType' => Setting::kiosk('delivery_type', 'local'),
            'showProductImages' => Setting::showProductImages('kiosko'),
            'showCategoryImages' => Setting::showCategoryImages('kiosko'),
            'categoriesOnly' => Setting::categoriesOnly('kiosko'),
            'successTicketText' => Setting::kiosk('success_ticket_text', 'Su ticket se está imprimiendo'),
            'successPayText' => Setting::kiosk('success_pay_text', 'Pase a caja para pagar'),
            'successPrepareText' => Setting::kiosk('success_prepare_text', 'Su pedido comenzará a prepararse después del pago'),
        ]);
    }

    public function update(Request $request)
    {
        $this->authorize('permission', 'view_restaurant');

        $validated = $request->validate([
            'kiosk_table_name' => 'required|string|max:100',
            'delivery_type' => 'required|in:local,web',
            'success_ticket_text' => 'required|string|max:200',
            'success_pay_text' => 'required|string|max:200',
            'success_prepare_text' => 'required|string|max:200',
        ]);

        $company = Company::getMainCompany();
        $kioskTable = RestaurantTable::where('company_id', $company->id)
            ->where('is_for_kiosko', true)
            ->first();

        if ($kioskTable) {
            $kioskTable->update(['name' => $validated['kiosk_table_name']]);
        } else {
            $floorId = Floor::where('company_id', $company->id)->value('id');

            if (!$floorId) {
                return back()->with('error', 'Primero cree un piso en Restaurante → Pisos para poder configurar la mesa kiosko.');
            }

            RestaurantTable::create([
                'company_id' => $company->id,
                'floor_id' => $floorId,
                'name' => $validated['kiosk_table_name'],
                'capacity' => 1,
                'status' => 'AVAILABLE',
                'is_for_kiosko' => true,
            ]);
        }

        Setting::setShowProductImages('kiosko', $request->boolean('img_products_kiosko'));
        Setting::setShowCategoryImages('kiosko', $request->boolean('img_categories_kiosko'));
        Setting::setCategoriesOnly('kiosko', $request->boolean('catonly_kiosko'));
        Setting::setKiosk('delivery_type', $validated['delivery_type']);
        Setting::setKiosk('success_ticket_text', $validated['success_ticket_text']);
        Setting::setKiosk('success_pay_text', $validated['success_pay_text']);
        Setting::setKiosk('success_prepare_text', $validated['success_prepare_text']);

        return redirect()->route('kiosk-settings.edit')
            ->with('success', 'Configuración del Kiosko guardada correctamente.');
    }
}
