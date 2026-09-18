<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingService extends Model
{
    use HasFactory, HasTranslations;

    protected $translatable = ['title', 'description'];

    protected $fillable = ['title', 'description', 'icon', 'image', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
