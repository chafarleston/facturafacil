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
}