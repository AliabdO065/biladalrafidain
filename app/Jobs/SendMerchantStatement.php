<?php

namespace App\Jobs;

use App\Models\MerchantStatement;
use App\Services\WhatsAppAccounts;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendMerchantStatement implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $timeout = 45;
    public int $statementId;

    public function __construct(int $statementId)
    {
        $this->statementId = $statementId;
        $this->onConnection('database')->onQueue('whatsapp');
    }

    public function handle(WhatsAppAccounts $whatsapp): void
    {
        // An atomic claim also protects against duplicate queue deliveries.
        $claimed = MerchantStatement::whereKey($this->statementId)->where('status', 'queued')->update(['status' => 'processing']);
        if (! $claimed) {
            return;
        }
        $statement = MerchantStatement::with('batch')->findOrFail($this->statementId);
        try {
            $result = $whatsapp->send($statement);
        } catch (Throwable $e) {
            // A timeout can happen after WhatsApp accepted the message. Never retry it automatically.
            $result = ['status' => 'unknown', 'last_error' => 'Delivery result is uncertain. Check WhatsApp before resending.'];
        }
        $statement->update($result);
    }

    public function failed(?Throwable $exception): void
    {
        MerchantStatement::whereKey($this->statementId)->where('status', 'processing')->update([
            'status' => 'unknown', 'last_error' => 'Worker stopped during sending. Check WhatsApp before resending.',
        ]);
    }
}
