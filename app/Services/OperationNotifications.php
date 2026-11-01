<?php

namespace App\Services;

use App\Jobs\SendOperationNotification;
use App\Models\LandingShipment;
use App\Models\NotificationTemplate;
use App\Models\TradeVoucher;
use App\Models\TradingContact;
use App\Models\WhatsappMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OperationNotifications
{
    public function voucher(TradeVoucher $voucher, bool $refresh = false): WhatsappMessage
    {
        $contact = $voucher->contact;
        $kind = $voucher->kind === 'supplier_payment' ? 'supplier_payment' : 'receipt';

        return $this->create($kind, $contact, 'voucher', $voucher->id, [
            'name' => $contact->name, 'reference' => $voucher->reference, 'date' => $voucher->occurred_at->format('Y-m-d'),
            'supplier' => $voucher->supplier ?: '—', 'amount' => $voucher->amount, 'currency' => $voucher->currency,
            'category' => $voucher->kind === 'shipping_receipt' ? 'الشحن' : 'المشتريات',
        ], $refresh);
    }

    public function shipment(LandingShipment $shipment, bool $refresh = false): ?WhatsappMessage
    {
        if (! $contact = TradingContact::fromShipment($shipment)) {
            return null;
        }

        return $this->create('shipment', $contact, 'shipment', $shipment->id, [
            'name' => $shipment->receiver_name, 'reference' => $shipment->tracking_code,
            'date' => ($shipment->receipt_date ?? $shipment->created_at)->format('Y-m-d'),
            'parcels' => $shipment->packages_count ?? '—', 'weight' => $shipment->weight_kg ?? '—',
        ], $refresh);
    }

    private function create(string $kind, TradingContact $contact, string $source, int $id, array $values, bool $refresh = false): WhatsappMessage
    {
        $template = NotificationTemplate::forKind($kind);
        if (isset($values['category'])) {
            $values['category'] = app('translator')->get($values['category'] === 'الشحن' ? 'trade.shipping_category' : 'trade.purchase_category', [], $template->language === 'ar' ? 'ar' : 'en');
        }
        $replacements = [];
        foreach ($values as $key => $value) {
            $replacements['{'.$key.'}'] = (string) $value;
        }
        $attributes = [
            'contact_id' => $contact->id, 'phone' => $contact->phone, 'kind' => $kind, 'source_type' => $source, 'source_id' => $id,
            'body' => strtr($template->body, $replacements), 'parameters' => array_map(fn ($field) => (string) ($values[$field] ?? '—'), $template->fields()),
            'provider_name' => $template->provider_name, 'language' => $template->language, 'status' => 'draft',
            'source_version' => $source === 'shipment' ? self::shipmentVersion(LandingShipment::findOrFail($id)) : TradeVoucher::findOrFail($id)->fingerprint,
        ];
        $message = WhatsappMessage::firstOrCreate(['dedupe_key' => hash('sha256', $source.':'.$id)], $attributes);
        if ($refresh && ! $message->wasRecentlyCreated) {
            if (! WhatsappMessage::whereKey($message->id)->whereIn('status', ['draft', 'failed'])->update($attributes + ['error' => null])) {
                $this->error('trade.template_changed');
            }

            return $message->fresh();
        }
        if ($message->wasRecentlyCreated && $template->auto_send && $template->isVerified() && app(WhatsAppMessaging::class)->configured() && $contact->opt_in && ! $contact->phone_conflict && $contact->phone) {
            $this->queue($message);
        }

        return $message;
    }

    public function queue(WhatsappMessage $message): void
    {
        DB::transaction(function () use ($message) {
            $message = WhatsappMessage::lockForUpdate()->findOrFail($message->id);
            if (! in_array($message->status, ['draft', 'failed'], true)) {
                return;
            }
            $contact = $message->contact;
            $template = NotificationTemplate::forKind($message->kind);
            if (! $contact || ! $contact->phone || ! $contact->opt_in || $contact->phone_conflict) {
                $this->error('trade.review_phone');
            }
            if (! app(WhatsAppMessaging::class)->configured()) {
                $this->error('trade.not_configured');
            }
            if (! $template->isVerified() || $message->provider_name !== $template->provider_name || $message->language !== $template->language) {
                $this->error('trade.verify_template');
            }
            // A changed template must not silently send a different approved message.
            preg_match_all('/\{([a-z_]+)\}/', $template->body, $matches);
            $body = $template->body;
            foreach ($template->fields() as $index => $field) {
                $body = str_replace('{'.$field.'}', $message->parameters[$index] ?? '', $body);
            }
            if ($body !== $message->body) {
                $this->error('trade.template_changed');
            }
            if ($message->kind === 'shipment' && ! app(ShipmentDocument::class)->binary()) {
                $this->error('trade.no_renderer');
            }
            $message->update(['phone' => $contact->phone, 'status' => 'queued', 'error' => null]);
            SendOperationNotification::dispatch($message->id)->onConnection('database')->onQueue('whatsapp')->afterCommit();
        });
    }

    private function error(string $key): void
    {
        throw ValidationException::withMessages(['message' => __($key)]);
    }

    public function refresh(WhatsappMessage $message): void
    {
        if (! in_array($message->status, ['draft', 'failed'], true)) {
            $this->error('trade.template_changed');
        }
        if ($message->source_type === 'shipment') {
            if (! $this->shipment(LandingShipment::findOrFail($message->source_id), true)) {
                $this->error('trade.review_phone');
            }
        } elseif ($message->source_type === 'voucher') {
            $this->voucher(TradeVoucher::findOrFail($message->source_id), true);
        } else {
            $this->error('trade.template_changed');
        }
    }

    public static function shipmentVersion(LandingShipment $shipment): string
    {
        return hash('sha256', json_encode($shipment->only(['tracking_code', 'receiver_name', 'merchant_number', 'sender_name', 'city', 'destination', 'trip_id', 'receipt_date', 'receipt_number', 'packages_count', 'weight_kg', 'shipping_fee', 'paid_amount', 'balance', 'currency', 'notes']), JSON_UNESCAPED_UNICODE));
    }
}
