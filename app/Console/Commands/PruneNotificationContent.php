<?php

namespace App\Console\Commands;

use App\Models\TradeImport;
use App\Models\WhatsappMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PruneNotificationContent extends Command
{
    protected $signature = 'notifications:prune';

    protected $description = 'Expire previews and old conversation bodies without deleting financial records or deduplication keys';

    public function handle(): int
    {
        $previews = TradeImport::whereNull('confirmed_at')->whereNotNull('payload')->where('updated_at', '<', now()->subDay())->update(['payload' => null]);
        $days = max(7, (int) config('whatsapp.conversation_days', 90));
        DB::table('whatsapp_delivery_events')->where('updated_at', '<', now()->subDays(7))->delete();
        $messages = WhatsappMessage::where('updated_at', '<', now()->subDays($days))->whereIn('status', ['received', 'accepted', 'sent', 'delivered', 'read', 'cancelled'])
            ->whereNotNull('body')->update(['body' => null, 'parameters' => null]);
        $root = storage_path('app/private/documents');
        if (is_dir($root)) {
            foreach (File::directories($root) as $directory) {
                // Crash leftovers only; active renders have a 40-second timeout.
                if (! is_link($directory) && filemtime($directory) < now()->subDay()->timestamp && str_starts_with(realpath($directory), realpath($root).DIRECTORY_SEPARATOR)) {
                    File::deleteDirectory($directory);
                }
            }
        }
        $this->info("Expired previews: $previews; pruned message bodies: $messages. Financial vouchers and message identifiers retained.");

        return 0;
    }
}
