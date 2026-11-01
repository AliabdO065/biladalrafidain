<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingTrip extends Model
{
    protected $fillable = ['reference', 'origin', 'destination', 'status', 'departure_date', 'estimated_delivery'];
    protected $casts = ['departure_date' => 'date', 'estimated_delivery' => 'date'];

    public function shipments()
    {
        return $this->hasMany(LandingShipment::class, 'trip_id');
    }

    public function events()
    {
        return $this->hasMany(LandingTripEvent::class, 'trip_id')->orderBy('id');
    }
}
