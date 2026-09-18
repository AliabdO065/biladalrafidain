<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingReview extends Model
{
    use HasFactory, HasTranslations;

    protected $translatable = ['review_text'];

    protected $fillable = [
        'author_name', 'author_photo', 'rating', 'review_date', 'review_text', 'is_placeholder', 'sort_order',
    ];

    protected $casts = [
        'rating' => 'integer',
        'review_date' => 'date',
        'is_placeholder' => 'boolean',
    ];
}
