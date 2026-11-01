<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Language extends Model
{
    use HasFactory;

    public const SUPPORTED = ['ar', 'en'];

    public function scopeSupported(Builder $query): Builder
    {
        return $query->whereIn('code', self::SUPPORTED);
    }

    protected $fillable = ['code', 'name', 'native_name', 'is_enabled', 'is_default', 'sort_order'];

    protected $casts = [
        'is_enabled' => 'boolean',
        'is_default' => 'boolean',
    ];
}
