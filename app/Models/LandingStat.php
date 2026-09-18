<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingStat extends Model
{
    use HasFactory, HasTranslations;

    protected $translatable = ['label'];

    protected $fillable = ['value', 'label', 'icon', 'sort_order'];
}
