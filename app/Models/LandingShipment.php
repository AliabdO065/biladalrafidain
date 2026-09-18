<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingShipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tracking_code', 'sender_name', 'receiver_name',
        'origin', 'destination', 'status', 'estimated_delivery',
    ];

    protected $casts = [
        'estimated_delivery' => 'date',
    ];

    public function events()
    {
        return $this->hasMany(LandingShipmentEvent::class, 'shipment_id')->orderBy('happened_at');
    }
}
