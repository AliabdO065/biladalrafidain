<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingStep extends Model
{
    use HasFactory, HasTranslations;

    protected $translatable = ['title', 'description'];

    protected $fillable = ['step_number', 'title', 'description', 'image', 'sort_order'];
}
