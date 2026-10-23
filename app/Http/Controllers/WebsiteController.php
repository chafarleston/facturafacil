<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\Setting;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WebsiteController extends Controller
{
    /**
     * Valores de la web pública (settings con defaults de la empresa).
     */
    private function defaults(): array
    {
        $company = Company::getMainCompany();

        return [
            'template' => Setting::web('template', 'modern'),
            'font' => Setting::web('font', 'Poppins'),
            'primary_color' => Setting::web('primary_color', '#e94560'),
            'secondary_color' => Setting::web('secondary_color', '#3a1c4a'),
            'hero_title' => Setting::web('hero_title', $company->nombre_comercial ?: $company->razon_social),
            'hero_subtitle' => Setting::web('hero_subtitle', ''),
            'hero_description' => Setting::web('hero_description', ''),
            'hero_image' => Setting::web('hero_image', ''),
            'about_title' => Setting::web('about_title', 'Nosotros'),
            'about_text' => Setting::web('about_text', ''),
            'about_image' => Setting::web('about_image', ''),
            'features_title' => Setting::web('features_title', '¿Por qué elegirnos?'),
            'feature_1_title' => Setting::web('feature_1_title', 'Calidad garantizada'),
            'feature_1_text' => Setting::web('feature_1_text', 'Ingredientes frescos y preparación cuidadosa en cada pedido.'),
            'feature_2_title' => Setting::web('feature_2_title', 'Atención personalizada'),
            'feature_2_text' => Setting::web('feature_2_text', 'Te atendemos con la calidez que mereces.'),
            'feature_3_title' => Setting::web('feature_3_title', 'Pedidos fáciles'),
            'feature_3_text' => Setting::web('feature_3_text', 'Pide por WhatsApp y recibe donde estés.'),
            'show_catalog' => Setting::web('show_catalog', '1') === '1',
            'show_prices' => Setting::web('show_prices', '1') === '1',
            'catalog_title' => Setting::web('catalog_title', 'Nuestros Productos'),
            'gallery_title' => Setting::web('gallery_title', 'Galería'),
            'phone' => Setting::web('phone', $company->telefono ?? ''),
            'email' => Setting::web('email', $company->email ?? ''),
            'address' => Setting::web('address', $company->direccion ?? ''),
            'whatsapp' => Setting::web('whatsapp', ''),
            'facebook' => Setting::web('facebook', ''),
            'instagram' => Setting::web('instagram', ''),
            'footer_text' => Setting::web('footer_text', ''),
        ];
    }

    public function index()
    {
        if (! Setting::isWebsiteEnabled()) {
            return redirect('/login');
        }

        $company = Company::getMainCompany();
        $web = $this->defaults();

        $categories = Category::where('company_id', $company->id)
            ->whereIn('estado', ['ACTIVO', 'ACT'])
            ->orderBy('nombre')
            ->get();

        $products = Product::where('company_id', $company->id)
            ->where('estado', 'ACTIVO')
            ->orderBy('descripcion')
            ->get();

        $categoryNames = $categories->pluck('nombre', 'id');
        $gallery = $products->filter(fn ($p) => (bool) $p->imagen_url)->take(8)->values();
        $logoUrl = $company->getLogoUrl();

        $templates = ['modern', 'elegant', 'minimal'];
        $template = in_array($web['template'], $templates, true) ? $web['template'] : 'modern';

        return view('website.templates.' . $template, compact('company', 'web', 'categories', 'products', 'categoryNames', 'gallery', 'logoUrl'));
    }

    public function toggle()
    {
        Setting::setWebsiteEnabled(! Setting::isWebsiteEnabled());

        return back()->with('success', Setting::isWebsiteEnabled()
            ? 'Página web habilitada'
            : 'Página web deshabilitada');
    }

    public function edit()
    {
        $company = Company::getMainCompany();
        $web = $this->defaults();

        return view('website.settings', compact('company', 'web'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'hero_title' => 'nullable|string|max:150',
            'hero_subtitle' => 'nullable|string|max:200',
            'hero_description' => 'nullable|string|max:2000',
            'hero_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'about_title' => 'nullable|string|max:150',
            'about_text' => 'nullable|string|max:5000',
            'about_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'features_title' => 'nullable|string|max:150',
            'feature_1_title' => 'nullable|string|max:100',
            'feature_1_text' => 'nullable|string|max:300',
            'feature_2_title' => 'nullable|string|max:100',
            'feature_2_text' => 'nullable|string|max:300',
            'feature_3_title' => 'nullable|string|max:100',
            'feature_3_text' => 'nullable|string|max:300',
            'catalog_title' => 'nullable|string|max:150',
            'gallery_title' => 'nullable|string|max:150',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string|max:250',
            'whatsapp' => 'nullable|string|max:50',
            'facebook' => 'nullable|string|max:250',
            'instagram' => 'nullable|string|max:250',
            'footer_text' => 'nullable|string|max:500',
            'primary_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'template' => 'nullable|in:modern,elegant,minimal',
            'font' => 'nullable|in:Poppins,Montserrat,Raleway,Playfair Display,Lora,Nunito',
        ]);

        $textKeys = [
            'hero_title', 'hero_subtitle', 'hero_description', 'about_title', 'about_text',
            'features_title', 'feature_1_title', 'feature_1_text', 'feature_2_title', 'feature_2_text',
            'feature_3_title', 'feature_3_text',
            'catalog_title', 'gallery_title', 'phone', 'email', 'address', 'whatsapp', 'facebook', 'instagram',
            'footer_text', 'primary_color', 'secondary_color', 'template', 'font',
        ];

        foreach ($textKeys as $key) {
            Setting::setWeb($key, $validated[$key] ?? '');
        }

        Setting::setWeb('show_catalog', $request->boolean('show_catalog') ? '1' : '0');
        Setting::setWeb('show_prices', $request->boolean('show_prices') ? '1' : '0');

        // Imágenes: reemplazar, conservar o eliminar
        foreach (['hero_image' => 1600, 'about_image' => 1200] as $field => $maxDimension) {
            $current = Setting::web($field);

            if ($request->hasFile($field)) {
                if ($current) {
                    Storage::disk('public')->delete($current);
                }
                $optimizer = new ImageOptimizer($maxDimension, 82);
                Setting::setWeb($field, $optimizer->optimize($request->file($field), 'website'));
            } elseif ($request->boolean('remove_' . $field)) {
                if ($current) {
                    Storage::disk('public')->delete($current);
                }
                Setting::setWeb($field, '');
            }
        }

        return redirect()->route('website.edit')
            ->with('success', 'Configuración de la página web guardada correctamente.');
    }
}
