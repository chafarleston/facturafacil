<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        return Cache::remember('setting_' . $key, 300, function () use ($key, $default) {
            $row = static::find($key);
            return $row ? $row->value : $default;
        });
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        Cache::forget('setting_' . $key);
    }

    public static function isSystemLocked(): bool
    {
        return static::get('system_locked', '0') === '1';
    }

    public static function setSystemLocked(bool $locked): void
    {
        static::set('system_locked', $locked ? '1' : '0');
    }

    public static function isPrecuadreEnabled(): bool
    {
        return static::get('precuadre_enabled', '0') === '1';
    }

    public static function setPrecuadreEnabled(bool $enabled): void
    {
        static::set('precuadre_enabled', $enabled ? '1' : '0');
    }

    public static function isWebsiteEnabled(): bool
    {
        return static::get('website_enabled', '0') === '1';
    }

    public static function setWebsiteEnabled(bool $enabled): void
    {
        static::set('website_enabled', $enabled ? '1' : '0');
    }

    public static function web(string $key, $default = null)
    {
        return static::get('web_' . $key, $default);
    }

    public static function setWeb(string $key, $value): void
    {
        static::set('web_' . $key, $value);
    }

    public static function pos(string $key, $default = null)
    {
        return static::get('pos_' . $key, $default);
    }

    public static function setPos(string $key, $value): void
    {
        static::set('pos_' . $key, $value);
    }

    public static function kiosk(string $key, $default = null)
    {
        return static::get('kiosk_' . $key, $default);
    }

    public static function setKiosk(string $key, $value): void
    {
        static::set('kiosk_' . $key, $value);
    }

    public static function isVirtualPrebillEnabled(): bool
    {
        return static::get('precuenta_virtual_enabled', '0') === '1';
    }

    public static function setVirtualPrebillEnabled(bool $enabled): void
    {
        static::set('precuenta_virtual_enabled', $enabled ? '1' : '0');
    }

    public static function virtualPrebillDomain(): string
    {
        return (string) static::get('precuenta_virtual_domain', '');
    }

    public static function setVirtualPrebillDomain(string $domain): void
    {
        static::set('precuenta_virtual_domain', rtrim(trim($domain), '/'));
    }

    public static function isVirtualMenuEnabled(): bool
    {
        return static::get('menu_virtual_enabled', '0') === '1';
    }

    public static function setVirtualMenuEnabled(bool $enabled): void
    {
        static::set('menu_virtual_enabled', $enabled ? '1' : '0');
    }

    public static function virtualMenuDomain(): string
    {
        return (string) static::get('menu_virtual_domain', '');
    }

    public static function setVirtualMenuDomain(string $domain): void
    {
        static::set('menu_virtual_domain', rtrim(trim($domain), '/'));
    }

    public static function virtualMenuNames(): array
    {
        $names = json_decode((string) static::get('menu_virtual_names', '[]'), true);

        return is_array($names) ? array_values($names) : [];
    }

    public static function setVirtualMenuNames(array $names): void
    {
        static::set('menu_virtual_names', json_encode(array_values($names)));
    }

    public static function virtualMenuSecondNames(): array
    {
        $names = json_decode((string) static::get('menu_virtual_second_names', '[]'), true);

        return is_array($names) ? array_values($names) : [];
    }

    public static function setVirtualMenuSecondNames(array $names): void
    {
        static::set('menu_virtual_second_names', json_encode(array_values($names)));
    }

    public static function virtualMenuPrices(): array
    {
        $prices = json_decode((string) static::get('menu_virtual_prices', '[]'), true);

        return is_array($prices) ? array_values($prices) : [];
    }

    public static function setVirtualMenuPrices(array $prices): void
    {
        static::set('menu_virtual_prices', json_encode(array_values($prices)));
    }

    public static function showProductImages(string $module): bool
    {
        return static::get('img_products_' . $module, '0') === '1';
    }

    public static function showCategoryImages(string $module): bool
    {
        return static::get('img_categories_' . $module, '0') === '1';
    }

    public static function setShowProductImages(string $module, bool $enabled): void
    {
        static::set('img_products_' . $module, $enabled ? '1' : '0');
    }

    public static function setShowCategoryImages(string $module, bool $enabled): void
    {
        static::set('img_categories_' . $module, $enabled ? '1' : '0');
    }

    public static function categoriesOnly(string $module): bool
    {
        return static::get('catonly_' . $module, '0') === '1';
    }

    public static function setCategoriesOnly(string $module, bool $enabled): void
    {
        static::set('catonly_' . $module, $enabled ? '1' : '0');
    }
}