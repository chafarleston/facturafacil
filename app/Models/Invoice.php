<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'customer_id', 'tipo_documento', 'serie', 'numero',
        'fecha_emision', 'fecha_vencimiento', 'moneda',
        'gravado', 'exonerado', 'inafecto', 'exento', 'igv', 'total', 'total_letras',
        'subtotal',
        'observaciones', 'codigo_hash', 'xml_path', 'pdf_path', 'cdr_path',
        'sunat_response', 'sunat_estado', 'sunat_ticket', 'sunat_cdr',
        'sunat_serie', 'sunat_numero', 'sunat_fecha',
        'sunat_code', 'sunat_description', 'xml_firmado', 'estado_sunat',
        'metodo_pago', 'referencia_pago', 'exclude_from_totals'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
    
    public function creditNote()
    {
        return $this->belongsTo(Invoice::class, 'credit_note_id');
    }
    
    public function originalInvoice()
    {
        return $this->hasOne(Invoice::class, 'credit_note_id', 'id');
    }

    public function getFullNumberAttribute(): string
    {
        return $this->serie . '-' . str_pad($this->numero, 8, '0', STR_PAD_LEFT);
    }

    public function getDocumentTypeNameAttribute(): string
    {
        if ($this->tipo_documento === 'NV') {
            // Notas de Venta deben mostrarse como "Boleta de venta"
            return 'Boleta de venta';
        }
        $types = [
            '01' => 'Factura',
            '03' => 'Boleta',
            '07' => 'Nota de Crédito',
            '08' => 'Nota de Débito',
        ];
        return $types[$this->tipo_documento] ?? $this->tipo_documento;
    }

    // Nota de Venta helper
    public function isNotaVenta(): bool
    {
        return $this->tipo_documento === 'NV';
    }

    // Scope to exclude from totals if needed
    public function scopeExcludeFromTotals($query)
    {
        return $query->where('exclude_from_totals', false);
    }
}
