<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingFaq extends Model
{
    use HasFactory, HasTranslations;

    protected $translatable = ['question', 'answer'];

    protected $fillable = ['question', 'answer', 'sort_order'];
}
