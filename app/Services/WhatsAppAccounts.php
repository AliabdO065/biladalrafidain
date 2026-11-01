<?php

namespace App\Services;

use App\Models\MerchantStatement;
use Illuminate\Support\Facades\Http;

class WhatsAppAccounts
{
    public function configured(): bool
    {
        return filled(config('whatsapp.token'))
            && preg_match('/^[0-9]+$/D', (string) config('whatsapp.phone_number_id'))
            && preg_match('/^v[0-9]+\.[0-9]+$/D', (string) config('whatsapp.version'))
            && in_array(config('whatsapp.language'), ['ar', 'en', 'en_US', 'en_GB'], true)
            && preg_match('/^[a-z0-9_]+$/D', (string) config('whatsapp.template'));
    }

    public function send(MerchantStatement $statement): array
    {
        if (! $this->configured()) {
            return ['status' => 'failed', 'last_error' => 'WhatsApp service is not configured.'];
        }
        // Positional template parameters: name, period, amount, currency, details.
        $locale = config('whatsapp.language') === 'ar' ? 'ar' : 'en';
        $details = $statement->shipment_count !== null
            ? app('translator')->get('Shipping fees: :fee; collected: :paid; shipments: :count', ['fee' => $statement->shipping_fee, 'paid' => $statement->paid_amount, 'count' => $statement->shipment_count], $locale)
            : ($statement->details ?: ($locale === 'ar' ? 'كشف حساب' : 'Account statement'));
        $values = [$statement->name, $statement->batch->title, $statement->amount, $statement->currency, $details];
        $response = Http::withToken(config('whatsapp.token'))->acceptJson()->connectTimeout(10)->timeout(25)
            ->post('https://graph.facebook.com/' . config('whatsapp.version') . '/' . config('whatsapp.phone_number_id') . '/messages', [
                'messaging_product' => 'whatsapp',
                'to' => ltrim($statement->phone, '+'),
                'type' => 'template',
                'template' => [
                    'name' => config('whatsapp.template'),
                    'language' => ['code' => config('whatsapp.language')],
                    'components' => [['type' => 'body', 'parameters' => array_map(fn ($text) => ['type' => 'text', 'text' => (string) $text], $values)]],
                ],
            ]);
        if (! $response->successful()) {
            // Do not store provider response bodies, tokens or account details in error logs.
            return ['status' => $response->serverError() ? 'unknown' : 'failed', 'last_error' => 'WhatsApp request returned HTTP ' . $response->status() . ', code ' . (int) $response->json('error.code', 0) . '.'];
        }
        $id = $response->json('messages.0.id');
        if (! is_string($id) || $id === '') {
            return ['status' => 'unknown', 'last_error' => 'No message reference returned. Check WhatsApp before resending.'];
        }

        return ['status' => 'accepted', 'provider_message_id' => $id, 'accepted_at' => now(), 'last_error' => null];
    }
}
