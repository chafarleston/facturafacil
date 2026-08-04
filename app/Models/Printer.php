<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Printer extends Model
{
    protected $fillable = [
        'name',
        'type',
        'printer_name',
        'ip_address',
        'port',
        'paper_size',
        'assigned_to',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'port' => 'integer',
    ];

    public function widthChars(): int
    {
        return $this->paper_size === '58mm' ? 32 : 48;
    }
}
