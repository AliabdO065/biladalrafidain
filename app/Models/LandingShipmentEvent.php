<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingShipmentEvent extends Model
{
    use HasFactory, HasTranslations;

    protected $translatable = ['note'];

    protected $fillable = ['shipment_id', 'status', 'note', 'happened_at', 'sort_order'];

    protected $casts = [
        'happened_at' => 'datetime',
    ];

    public function shipment()
    {
        return $this->belongsTo(LandingShipment::class, 'shipment_id');
    }
}
