<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingLead extends Model
{
    use HasFactory;

    protected $fillable = [
        'problem_type', 'postal_code', 'name', 'phone', 'email', 'message', 'status',
    ];
}
