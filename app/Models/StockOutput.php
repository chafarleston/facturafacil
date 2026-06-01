<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockOutput extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'user_id', 'motivo', 'motivo_otro', 'referencia', 'notas',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(StockOutputItem::class);
    }
}
