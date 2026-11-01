<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TradeVoucher extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['amount' => 'decimal:2', 'occurred_at' => 'date'];

    public function contact()
    {
        return $this->belongsTo(TradingContact::class, 'contact_id');
    }
}
