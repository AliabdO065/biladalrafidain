<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingComparison extends Model
{
    use HasFactory, HasTranslations;

    protected $translatable = ['criterion', 'us_value', 'them_value'];

    protected $fillable = [
        'criterion', 'us_value', 'us_is_positive', 'them_value', 'them_is_positive', 'sort_order',
    ];

    protected $casts = [
        'us_is_positive' => 'boolean',
        'them_is_positive' => 'boolean',
    ];
}
