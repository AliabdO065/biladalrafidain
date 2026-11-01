<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappMessage extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['parameters' => 'array', 'provider_at' => 'datetime'];

    public function shipmentSource()
    {
        return $this->belongsTo(LandingShipment::class, 'source_id');
    }

    public function voucherSource()
    {
        return $this->belongsTo(TradeVoucher::class, 'source_id');
    }

    public function scopeOperationFilters($query, array $filters)
    {
        if (! empty($filters['trip_id'])) {
            $query->where('source_type', 'shipment')->whereIn('source_id', LandingShipment::where('trip_id', $filters['trip_id'])->select('id'));
        }
        if (! empty($filters['shipment_import_id'])) {
            $query->where('source_type', 'shipment')->whereIn('source_id', LandingShipment::where('import_id', $filters['shipment_import_id'])->select('id'));
        }
        if (! empty($filters['import_id'])) {
            $query->where('source_type', 'voucher')->whereIn('source_id', TradeVoucher::where('import_id', $filters['import_id'])->select('id'));
        }
        if (! empty($filters['contact_id'])) {
            $query->where('contact_id', $filters['contact_id']);
        }
        if (! empty($filters['shipment_id'])) {
            $query->where('source_type', 'shipment')->where('source_id', $filters['shipment_id']);
        }

        return $query;
    }

    public function contact()
    {
        return $this->belongsTo(TradingContact::class, 'contact_id');
    }
}
