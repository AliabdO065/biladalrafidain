<?php
namespace App\Console\Commands;

use App\Services\WhatsAppMessaging;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class HostingTick extends Command
{
    protected $signature = 'hosting:tick';
    protected $description = 'Run a bounded shared-hosting queue batch and due scheduled maintenance.';

    public function handle(WhatsAppMessaging $whatsapp): int
    {
        $lock = Cache::lock('hosting:tick-lock', 240);
        if (!$lock->get()) {
            $this->line('Another hosting tick is running.');
            return self::SUCCESS;
        }
        try {
            $maintenance = $this->call('schedule:run');
            $queue = self::SUCCESS;
            if ($whatsapp->configured() && !config('whatsapp.paused')) {
                $queue = $this->call('queue:work', [
                    'connection' => 'database', '--queue' => 'whatsapp', '--stop-when-empty' => true,
                    '--max-jobs' => max(1, min(50, config('hosting.jobs_per_tick'))),
                    '--max-time' => max(10, min(90, config('hosting.worker_seconds'))),
                    '--sleep' => 0, '--tries' => 1, '--timeout' => 110,
                ]);
            } else {
                $this->line('WhatsApp is disconnected or paused; queued notifications were left untouched.');
            }
            if ($maintenance !== self::SUCCESS || $queue !== self::SUCCESS) return self::FAILURE;
            Cache::put('hosting:last_tick', now()->toIso8601String(), now()->addDays(7));
            $this->info('Hosting tick completed.');
            return self::SUCCESS;
        } finally {
            $lock->release();
        }
    }
}
