<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\RestaurantTable;
use App\Models\Setting;
use App\Services\VirtualPrebillQrService;
use Illuminate\Http\Request;

class VirtualPrebillController extends Controller
{
    private function authorizeSettings(): void
    {
        $this->authorize('permission', 'view_restaurant');
    }

    public function index()
    {
        $this->authorizeSettings();

        $company = Company::getMainCompany();
        $enabled = Setting::isVirtualPrebillEnabled();
        $domain = Setting::virtualPrebillDomain();

        $tables = collect();

        if ($enabled && $domain !== '') {
            $tables = RestaurantTable::where('company_id', $company->id)
                ->where('is_for_kiosko', false)
                ->orderBy('name')
                ->get()
                ->map(function ($table) use ($domain) {
                    $url = rtrim($domain, '/') . '/' . $table->slug;

                    return [
                        'table' => $table,
                        'url' => $url,
                        'qr' => VirtualPrebillQrService::dataUri($url),
                    ];
                });
        }

        return view('virtualprebill.index', compact('company', 'enabled', 'domain', 'tables'));
    }

    public function update(Request $request)
    {
        $this->authorizeSettings();

        $validated = $request->validate([
            'domain' => 'required|url|max:255',
            'enabled' => 'nullable|boolean',
        ]);

        Setting::setVirtualPrebillEnabled($request->boolean('enabled'));
        Setting::setVirtualPrebillDomain($validated['domain']);

        return redirect()->route('virtual-prebill.index')
            ->with('success', 'Configuración de Precuenta Virtual guardada.');
    }

    public function show($slug)
    {
        if (! Setting::isVirtualPrebillEnabled()) {
            abort(404);
        }

        $company = Company::getMainCompany();

        $table = RestaurantTable::where('company_id', $company->id)
            ->where('is_for_kiosko', false)
            ->where('slug', $slug)
            ->first();

        if (! $table) {
            abort(404);
        }

        $order = $table->activeOrder();

        if (! $order) {
            return view('virtualprebill.show', [
                'company' => $company,
                'table' => $table,
                'order' => null,
            ]);
        }

        $order->load(['items', 'table.floor', 'user']);
        $order->setRelation('items', $order->items
            ->where('kitchen_status', '!=', 'CANCELLED')
            ->whereNull('paid_invoice_id'));

        $companyRecord = Company::find($order->company_id) ?: $company;

        return view('virtualprebill.show', [
            'company' => $companyRecord,
            'table' => $table,
            'order' => $order,
        ]);
    }
}
