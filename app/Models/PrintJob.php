<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrintJob extends Model
{
    protected $fillable = [
        'printer_name', 'printer_ip', 'printer_port', 'type',
        'job_type', 'reference_type', 'reference_id',
        'data', 'status', 'error_message', 'attempts', 'completed_at',
    ];

    protected $casts = [
        'attempts' => 'integer',
        'completed_at' => 'datetime',
    ];
}
