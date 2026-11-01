<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerchantStatement extends Model
{
    protected $fillable = ['batch_id', 'merchant_id', 'name', 'phone', 'amount', 'currency', 'details', 'status', 'provider_message_id', 'last_error', 'accepted_at', 'shipment_count', 'shipping_fee', 'paid_amount', 'source_version', 'source_account_key'];
    protected $casts = ['amount' => 'decimal:2', 'accepted_at' => 'datetime', 'shipping_fee' => 'decimal:2', 'paid_amount' => 'decimal:2'];

    public function batch()
    {
        return $this->belongsTo(AccountBatch::class, 'batch_id');
    }

    public function messageText(): string
    {
        if ($this->shipment_count !== null) {
            return __('Shipment account message', [
                'name' => $this->name, 'period' => $this->batch->title, 'count' => $this->shipment_count,
                'fee' => $this->shipping_fee, 'paid' => $this->paid_amount, 'balance' => $this->amount,
                'currency' => $this->currency,
            ]);
        }
        return __('Merchant account message', [
            'name' => $this->name, 'period' => $this->batch->title, 'amount' => $this->amount,
            'currency' => $this->currency, 'details' => $this->details ?: __('Account statement'),
        ]);
    }

    public function getErrorMessageAttribute(): ?string
    {
        if (! $this->last_error) {
            return null;
        }
        if (preg_match('/^WhatsApp request returned HTTP ([0-9]+), code ([0-9]+)\.$/', $this->last_error, $match)) {
            return __('WhatsApp request returned HTTP :status, code :code.', ['status' => $match[1], 'code' => $match[2]]);
        }

        return __($this->last_error);
    }

    public function whatsappUrl(): string
    {
        return 'https://wa.me/' . ltrim($this->phone, '+') . '?text=' . rawurlencode($this->messageText());
    }

    public static function statusLabels(): array
    {
        return ['pending' => __('Awaiting review'), 'queued' => __('Queued'), 'processing' => __('Sending'), 'accepted' => __('Accepted by WhatsApp'), 'failed' => __('Failed'), 'unknown' => __('Needs delivery review'), 'duplicate' => __('Handled in another message')];
    }
}
