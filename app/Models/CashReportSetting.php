<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashReportSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'mostrar_lista_comprobantes', 'mostrar_productos_vendidos', 'mostrar_lineas_eliminadas',
    ];

    protected $casts = [
        'mostrar_lista_comprobantes' => 'boolean',
        'mostrar_productos_vendidos' => 'boolean',
        'mostrar_lineas_eliminadas' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}