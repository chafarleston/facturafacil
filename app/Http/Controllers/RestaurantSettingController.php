<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RestaurantSettingController extends Controller
{
    public function edit()
    {
        $this->authorize('permission', 'view_restaurant');

        return view('restaurantsettings.edit', [
            'showProductImages' => Setting::showProductImages('restaurant'),
            'showCategoryImages' => Setting::showCategoryImages('restaurant'),
            'categoriesOnly' => Setting::categoriesOnly('restaurant'),
            'kitchenFontSize' => Setting::restaurantKitchenFontSize(),
            'idleTimeout' => Setting::restaurantIdleTimeout(),
            'orderMode' => Company::orderMode(),
        ]);
    }

    public function update(Request $request)
    {
        $this->authorize('permission', 'view_restaurant');

        $request->validate([
            'idle_timeout_seconds' => 'nullable|integer|min:1|max:300',
        ]);

        Setting::setShowProductImages('restaurant', $request->boolean('img_products_restaurant'));
        Setting::setShowCategoryImages('restaurant', $request->boolean('img_categories_restaurant'));
        Setting::setCategoriesOnly('restaurant', $request->boolean('catonly_restaurant'));

        $kitchenFontSize = in_array($request->input('kitchen_font_size'), ['normal', 'medium', 'large'], true)
            ? $request->input('kitchen_font_size')
            : 'normal';
        Setting::setRestaurantKitchenFontSize($kitchenFontSize);

        $idleTimeout = $request->filled('idle_timeout_seconds')
            ? (int) $request->input('idle_timeout_seconds')
            : 15;
        Setting::setRestaurantIdleTimeout($idleTimeout);

        // Limpia la caché del catálogo del restaurante
        $companyId = Company::getMainCompany()->id;
        Cache::forget('restaurant_products_' . $companyId);
        Cache::forget('restaurant_categories_' . $companyId);

        return redirect()->route('restaurant-settings.edit')
            ->with('success', 'Configuración del Restaurante guardada correctamente.');
    }
}
