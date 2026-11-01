<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingTripEvent extends Model
{
    public $timestamps = false;
    protected $fillable = ['status', 'happened_at'];
    protected $casts = ['happened_at' => 'datetime'];
}
