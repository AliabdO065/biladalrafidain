<?php

namespace App\Models;

use App\Support\ShipmentAccountIdentity;
use Illuminate\Database\Eloquent\Model;

class TradingContact extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['opt_in' => 'boolean', 'phone_conflict' => 'boolean'];

    public static function fromShipment(LandingShipment $shipment): ?self
    {
        if (! filled($shipment->merchant_number)) {
            return null;
        }
        $contact = self::firstOrCreate(['number' => $shipment->merchant_number], ['name' => $shipment->receiver_name ?: $shipment->merchant_number]);
        $phone = ShipmentAccountIdentity::phone((string) $shipment->receiver_phone);
        if ($phone && ! $contact->phone) {
            $contact->update(['phone' => $phone]);
        } elseif ($phone && $phone !== $contact->phone) {
            $contact->update(['phone_conflict' => true]);
        }

        return $contact;
    }
}
