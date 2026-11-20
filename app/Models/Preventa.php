<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Preventa extends Model
{
    use HasFactory;

    public const TIPO_COTIZACION = 'cotizacion';
    public const TIPO_CONTRATO = 'contrato';
    public const TIPO_PEDIDO = 'pedido';
    public const TIPO_SOPORTE = 'soporte';

    public const TIPOS = [
        self::TIPO_COTIZACION => 'Cotizaciones',
        self::TIPO_CONTRATO => 'Contratos',
        self::TIPO_PEDIDO => 'Pedidos',
        self::TIPO_SOPORTE => 'Soporte técnico',
    ];

    public const PREFIJOS = [
        self::TIPO_COTIZACION => 'COT',
        self::TIPO_CONTRATO => 'CON',
        self::TIPO_PEDIDO => 'PED',
        self::TIPO_SOPORTE => 'SOP',
    ];

    public const ESTADOS = [
        'BORRADOR' => 'Borrador',
        'ENVIADO' => 'Enviado',
        'APROBADO' => 'Aprobado',
        'RECHAZADO' => 'Rechazado',
        'VIGENTE' => 'Vigente',
        'CONVERTIDO' => 'Convertido en pedido',
        'PENDIENTE' => 'Pendiente',
        'EN_PROCESO' => 'En proceso',
        'ENTREGADO' => 'Entregado',
        'ATENDIDO' => 'Atendido',
        'CERRADO' => 'Cerrado',
        'CANCELADO' => 'Cancelado',
    ];

    protected $fillable = [
        'company_id',
        'user_id',
        'customer_id',
        'invoice_id',
        'parent_id',
        'tipo',
        'document_type',
        'payment_condition',
        'credit_days',
        'numero',
        'cliente_nombre',
        'cliente_documento',
        'cliente_email',
        'cliente_telefono',
        'titulo',
        'descripcion',
        'monto',
        'subtotal',
        'igv',
        'estado',
        'fecha',
        'fecha_vencimiento',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_vencimiento' => 'date',
        'monto' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'igv' => 'decimal:2',
        'credit_days' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function items()
    {
        return $this->hasMany(PreventaItem::class);
    }

    public function documentTypeLabel(): string
    {
        return match ($this->document_type) {
            '01' => 'Factura',
            '03' => 'Boleta',
            default => '-',
        };
    }

    public function paymentConditionLabel(): string
    {
        return match ($this->payment_condition) {
            'CONTADO' => 'Contado',
            'CREDITO' => 'Crédito' . ($this->credit_days ? ' a ' . $this->credit_days . ' días' : ''),
            default => '-',
        };
    }

    public static function tipos(): array
    {
        return self::TIPOS;
    }

    public static function estados(): array
    {
        return self::ESTADOS;
    }

    public function tipoLabel(): string
    {
        return self::TIPOS[$this->tipo] ?? ucfirst($this->tipo);
    }

    public function estadoLabel(): string
    {
        return self::ESTADOS[$this->estado] ?? ucfirst(strtolower($this->estado));
    }

    public static function nextNumero(int $companyId, string $tipo): string
    {
        $prefix = self::PREFIJOS[$tipo] ?? 'PRE';
        $last = static::where('company_id', $companyId)
            ->where('tipo', $tipo)
            ->orderByDesc('id')
            ->value('numero');

        $sequence = 1;

        if ($last && preg_match('/(\d+)$/', $last, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return $prefix . '-' . str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
    }
}
