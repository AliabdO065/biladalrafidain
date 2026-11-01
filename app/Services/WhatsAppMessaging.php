<?php

namespace App\Services;

use App\Models\LandingShipment;
use App\Models\NotificationTemplate;
use App\Models\WhatsappMessage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class WhatsAppMessaging
{
    public function configured(): bool
    {
        return filled(config('whatsapp.token')) && preg_match('/^[0-9]+$/D', (string) config('whatsapp.phone_number_id'))
            && preg_match('/^v[0-9]+\.[0-9]+$/D', (string) config('whatsapp.version'));
    }

    private function url(string $resource): string
    {
        return 'https://graph.facebook.com/'.config('whatsapp.version').'/'.$resource;
    }

    private function client()
    {
        return Http::withToken(config('whatsapp.token'))->acceptJson()->connectTimeout(10)->timeout(25);
    }

    public function verify(NotificationTemplate $template): void
    {
        if (! $this->configured() || ! preg_match('/^[0-9]+$/D', (string) config('whatsapp.business_id')) || ! $template->provider_name) {
            $this->error('trade.not_configured');
        }
        $response = $this->client()->get($this->url(config('whatsapp.business_id').'/message_templates'), ['name' => $template->provider_name, 'fields' => 'name,status,language,components']);
        if (! $response->successful()) {
            $this->error('trade.verify_failed');
        }
        $approved = false;
        foreach ($response->json('data', []) as $candidate) {
            if (($candidate['name'] ?? '') !== $template->provider_name || ($candidate['language'] ?? '') !== $template->language || ($candidate['status'] ?? '') !== 'APPROVED') {
                continue;
            }
            $components = collect($candidate['components'] ?? []);
            $body = $components->firstWhere('type', 'BODY');
            $header = $components->firstWhere('type', 'HEADER');
            if (str_replace(["\r\n", "\r"], "\n", $body['text'] ?? '') !== $template->providerBody()) {
                continue;
            }
            if ($template->kind === 'shipment' ? (($header['format'] ?? '') !== 'IMAGE') : (bool) $header) {
                continue;
            }
            if ($components->contains('type', 'BUTTONS')) {
                continue;
            } // Unsupported variable button/header structures are never guessed.
            $approved = true;
        }
        if (! $approved) {
            $this->error('trade.verify_failed');
        }
        $template->update(['verified_hash' => $template->contentHash()]);
    }

    public function canReply(string $phone): bool
    {
        return WhatsappMessage::where('direction', 'in')->where('phone', $phone)->where('provider_at', '>', now()->subHours(24))->where('provider_at', '<=', now())->exists();
    }

    public function send(WhatsappMessage $message): array
    {
        if (! $this->configured()) {
            return ['status' => 'failed', 'error' => 'WhatsApp service is not configured.'];
        }
        if (config('whatsapp.paused')) {
            return ['status' => 'failed', 'error' => 'Notifications are paused.'];
        }
        $payload = ['messaging_product' => 'whatsapp', 'to' => ltrim((string) $message->phone, '+')];
        if ($message->kind === 'reply') {
            if (! $this->canReply($message->phone)) {
                return ['status' => 'failed', 'error' => 'The customer service window is closed.'];
            }
            $payload += ['type' => 'text', 'text' => ['body' => $message->body]];
        } else {
            $contact = $message->contact;
            $template = NotificationTemplate::forKind($message->kind);
            if (! $contact || ! $contact->opt_in || $contact->phone_conflict || $contact->phone !== $message->phone) {
                return ['status' => 'failed', 'error' => 'Review recipient before sending.'];
            }
            if (! $template->isVerified() || $template->provider_name !== $message->provider_name || $template->language !== $message->language) {
                return ['status' => 'failed', 'error' => 'Template changed or is not verified.'];
            }
            $body = $template->body;
            foreach ($template->fields() as $index => $field) {
                $body = str_replace('{'.$field.'}', $message->parameters[$index] ?? '', $body);
            }
            if ($body !== $message->body) {
                return ['status' => 'failed', 'error' => 'Template changed.'];
            }
            $components = [['type' => 'body', 'parameters' => array_map(fn ($value) => ['type' => 'text', 'text' => (string) $value], $message->parameters ?? [])]];
            if ($message->kind === 'shipment') {
                $shipment = LandingShipment::find($message->source_id);
                if (! $shipment || ! hash_equals((string) $message->source_version, OperationNotifications::shipmentVersion($shipment))) {
                    return ['status' => 'failed', 'error' => 'Shipment changed. Review before sending.'];
                }
                try {
                    $document = app(ShipmentDocument::class)->render($shipment, $message->language);
                } catch (\Throwable $error) {
                    return ['status' => 'failed', 'error' => 'Document rendering failed.'];
                }
                try {
                    if (filesize($document['path']) > 5 * 1024 * 1024) {
                        return ['status' => 'failed', 'error' => 'Document exceeds image size limit.'];
                    }
                    $handle = fopen($document['path'], 'rb');
                    try {
                        $upload = $this->client()->attach('file', $handle, 'shipment.png', ['Content-Type' => 'image/png'])->post($this->url(config('whatsapp.phone_number_id').'/media'), ['messaging_product' => 'whatsapp', 'type' => 'image/png']);
                    } finally {
                        fclose($handle);
                    }
                    if (! $upload->successful() || ! is_string($upload->json('id'))) {
                        return ['status' => 'failed', 'error' => 'Document upload failed.'];
                    }
                    $components[] = ['type' => 'header', 'parameters' => [['type' => 'image', 'image' => ['id' => $upload->json('id')]]]];
                } catch (\Throwable $error) {
                    return ['status' => 'failed', 'error' => 'Document upload failed.'];
                } finally {
                    File::deleteDirectory($document['folder']);
                }
            }
            $payload += ['type' => 'template', 'template' => ['name' => $message->provider_name, 'language' => ['code' => $message->language], 'components' => $components]];
        }
        $response = $this->client()->post($this->url(config('whatsapp.phone_number_id').'/messages'), $payload);
        if (! $response->successful()) {
            return ['status' => $response->serverError() ? 'unknown' : 'failed', 'error' => 'Provider HTTP '.$response->status().', code '.(int) $response->json('error.code', 0)];
        }
        if (! is_string($id = $response->json('messages.0.id')) || ! $id) {
            return ['status' => 'unknown', 'error' => 'No message reference returned.'];
        }

        return ['status' => 'accepted', 'provider_id' => $id, 'error' => null];
    }

    private function error(string $key): void
    {
        throw ValidationException::withMessages(['template' => __($key)]);
    }
}
