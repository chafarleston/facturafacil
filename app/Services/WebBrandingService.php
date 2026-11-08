<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Setting;

class WebBrandingService
{
    public function for(Company $company): array
    {
        $templates = ['modern', 'elegant', 'minimal', 'classic', 'dark', 'tropical', 'chifa'];
        $template = Setting::web('template', 'modern');
        if (!in_array($template, $templates, true)) {
            $template = 'modern';
        }

        $fonts = ['Poppins', 'Montserrat', 'Raleway', 'Playfair Display', 'Lora', 'Nunito'];
        $font = Setting::web('font', 'Poppins');
        if (!in_array($font, $fonts, true)) {
            $font = 'Poppins';
        }

        $logo = Setting::web('logo', '');
        $logoUrl = $logo ? asset('storage/' . $logo) : $company->getLogoUrl();

        $primaryColor = Setting::web('primary_color', '#e94560') ?: '#e94560';
        $secondaryColor = Setting::web('secondary_color', '#3a1c4a') ?: '#3a1c4a';

        if ($template === 'chifa') {
            if ($primaryColor === '#e94560') {
                $primaryColor = '#9e1b1b';
            }
            if ($secondaryColor === '#3a1c4a') {
                $secondaryColor = '#d4af37';
            }
        }

        return [
            'template' => $template,
            'font' => $font,
            'fontUrl' => 'https://fonts.googleapis.com/css2?family=' . urlencode($font) . ':wght@400;500;600;700&display=swap',
            'primary_color' => $primaryColor,
            'secondary_color' => $secondaryColor,
            'logo' => $logo,
            'logoUrl' => $logoUrl,
            'logo_height' => (int) (Setting::web('logo_height', 56) ?: 56),
            'company_name' => $company->nombre_comercial ?: $company->razon_social,
        ];
    }
}
