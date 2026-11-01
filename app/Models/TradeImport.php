<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TradeImport extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['payload' => 'array', 'confirmed_at' => 'datetime'];
}
