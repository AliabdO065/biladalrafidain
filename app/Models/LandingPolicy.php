<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingPolicy extends Model
{
    use HasFactory, HasTranslations;

    protected $translatable = ['title', 'body'];

    protected $fillable = ['slug', 'title', 'body', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
