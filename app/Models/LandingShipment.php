<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Support\ShipmentAccountIdentity;

class LandingShipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tracking_code', 'sender_name', 'receiver_name',
        'origin', 'destination', 'status', 'estimated_delivery', 'trip_id',
        'import_id', 'source_fingerprint', 'source_row', 'report_sequence', 'receipt_number', 'receipt_date',
        'factory_number', 'merchant_number', 'receiver_phone', 'city', 'packages_count',
        'weight_kg', 'item_price', 'shipping_fee', 'paid_amount', 'balance', 'total', 'notes',
        'currency',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $shipment) {
            $shipment->account_key = ShipmentAccountIdentity::key($shipment->getAttributes());
            $shipment->currency = $shipment->currency ? strtoupper($shipment->currency) : null;
        });
        static::saved(function (self $shipment) {
            if ($shipment->wasChanged(['merchant_number','receiver_phone','receiver_name']) || $shipment->wasRecentlyCreated) TradingContact::fromShipment($shipment);
        });
    }

    protected $casts = [
        'estimated_delivery' => 'date',
        'receipt_date' => 'date',
        'packages_count' => 'integer',
        'weight_kg' => 'decimal:3',
        'item_price' => 'decimal:4',
        'shipping_fee' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function events()
    {
        return $this->hasMany(LandingShipmentEvent::class, 'shipment_id')->orderBy('happened_at');
    }

    public function trip()
    {
        return $this->belongsTo(LandingTrip::class, 'trip_id');
    }

    public function getCurrentStatusAttribute(): string
    {
        return $this->trip ? $this->trip->status : $this->status;
    }
}
