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
        'assigned_to',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'port' => 'integer',
    ];
}
