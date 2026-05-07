<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ubigeo extends Model
{
    public $timestamps = false;
    
    protected $table = 'ubigeos';
    
    protected $fillable = [
        'codigo', 'departamento', 'provincia', 'distrito'
    ];

    public static function getDepartamentos()
    {
        return self::select('departamento')
            ->distinct()
            ->orderBy('departamento')
            ->pluck('departamento');
    }

    public static function getProvincias($departamento)
    {
        return self::where('departamento', $departamento)
            ->select('provincia')
            ->distinct()
            ->orderBy('provincia')
            ->pluck('provincia');
    }

    public static function getDistritos($departamento, $provincia)
    {
        return self::where('departamento', $departamento)
            ->where('provincia', $provincia)
            ->orderBy('distrito')
            ->get();
    }

    public static function getCodigo($departamento, $provincia, $distrito)
    {
        $ubigeo = self::where('departamento', $departamento)
            ->where('provincia', $provincia)
            ->where('distrito', $distrito)
            ->first();
        
        return $ubigeo ? $ubigeo->codigo : null;
    }
}