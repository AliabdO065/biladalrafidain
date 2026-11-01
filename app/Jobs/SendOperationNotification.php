<?php

namespace App\Jobs;

use App\Models\WhatsappMessage;
use App\Services\WhatsAppMessaging;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendOperationNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public $timeout = 100;

    public function __construct(public int $messageId)
    {
    }

    public function handle(WhatsAppMessaging $service): void
    {
        if (! WhatsappMessage::whereKey($this->messageId)->where('status', 'queued')->update(['status' => 'processing'])) {
            return;
        }
        try {
            $result = $service->send(WhatsappMessage::findOrFail($this->messageId));
        } catch (\Throwable $error) {
            $result = ['status' => 'unknown', 'error' => 'Delivery result is uncertain.'];
        }
        WhatsappMessage::whereKey($this->messageId)->where('status', 'processing')->update($result);
        if (isset($result['provider_id'])) {
            app(\App\Services\WhatsAppDelivery::class)->reconcile($result['provider_id']);
        }
    }

    public function failed(?\Throwable $exception): void
    {
        WhatsappMessage::whereKey($this->messageId)->whereIn('status', ['queued', 'processing'])->update(['status' => 'unknown', 'error' => 'Worker stopped. Check provider history before resending.']);
    }
}
