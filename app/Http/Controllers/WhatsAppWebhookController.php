<?php

namespace App\Http\Controllers;

use App\Models\WhatsappMessage;
use App\Support\ShipmentAccountIdentity;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class WhatsAppWebhookController extends Controller
{
    public function verify(Request $request)
    {
        abort_unless(filled(config('whatsapp.verify_token')) && $request->query('hub.mode', $request->query('hub_mode')) === 'subscribe' && hash_equals((string) config('whatsapp.verify_token'), (string) $request->query('hub.verify_token', $request->query('hub_verify_token'))), 403);

        return response((string) $request->query('hub.challenge', $request->query('hub_challenge')))->header('Content-Type', 'text/plain');
    }

    public function receive(Request $request)
    {
        $secret = (string) config('whatsapp.app_secret');
        abort_unless($secret && hash_equals('sha256='.hash_hmac('sha256', $request->getContent(), $secret), (string) $request->header('X-Hub-Signature-256')), 403);
        abort_if(strlen($request->getContent()) > 1024 * 1024, 413);
        foreach ($request->input('entry', []) as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                $value = $change['value'] ?? [];
                if (($value['metadata']['phone_number_id'] ?? null) !== (string) config('whatsapp.phone_number_id')) {
                    continue;
                }
                foreach ($value['messages'] ?? [] as $item) {
                    $phone = ShipmentAccountIdentity::phone('+'.($item['from'] ?? ''));
                    if (! $phone || ! is_string($id = $item['id'] ?? null) || strlen($id) > 191 || ! preg_match('/^[0-9]{1,10}$/D', (string) ($item['timestamp'] ?? ''))) {
                        continue;
                    }
                    $at = CarbonImmutable::createFromTimestampUTC((int) $item['timestamp']);
                    $body = ($item['type'] ?? '') === 'text' ? mb_substr((string) ($item['text']['body'] ?? ''), 0, 4000) : '['.mb_substr((string) ($item['type'] ?? 'media'), 0, 30).']';
                    WhatsappMessage::firstOrCreate(['provider_id' => $id], ['phone' => $phone, 'direction' => 'in', 'kind' => 'incoming', 'status' => 'received', 'body' => $body, 'provider_at' => $at]);
                }
                foreach ($value['statuses'] ?? [] as $status) {
                    if (! in_array($status['status'] ?? '', ['sent', 'delivered', 'read', 'failed'], true)) {
                        continue;
                    }
                    if (! is_string($status['id'] ?? null)) {
                        continue;
                    }
                    app(\App\Services\WhatsAppDelivery::class)->record($status['id'], $status['status'], $status['status'] === 'failed' ? 'Provider code '.(int) ($status['errors'][0]['code'] ?? 0) : null);
                }
            }
        }

        return response()->json(['received' => true]);
    }
}
