<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'native_name', 'is_enabled', 'is_default', 'sort_order'];

    protected $casts = [
        'is_enabled' => 'boolean',
        'is_default' => 'boolean',
    ];
}
