<?php

namespace App\Services;

use App\Models\WhatsappMessage;
use Illuminate\Support\Facades\DB;

class WhatsAppDelivery
{
    private const RANK = ['processing' => 0, 'accepted' => 0, 'unknown' => 0, 'failed' => 0, 'sent' => 1, 'delivered' => 2, 'read' => 3];

    public function record(string $id, string $status, ?string $error): void
    {
        if (! $id || strlen($id) > 191 || ! in_array($status, ['sent', 'delivered', 'read', 'failed'], true)) {
            return;
        }
        DB::transaction(function () use ($id, $status, $error) {
            $old = DB::table('whatsapp_delivery_events')->where('provider_id', $id)->lockForUpdate()->first();
            if ($old && self::RANK[$status] < self::RANK[$old->status]) {
                return;
            }
            DB::table('whatsapp_delivery_events')->updateOrInsert(['provider_id' => $id], ['status' => $status, 'error' => $error, 'created_at' => $old->created_at ?? now(), 'updated_at' => now()]);
        });
        $this->reconcile($id);
    }

    public function reconcile(string $id): void
    {
        DB::transaction(function () use ($id) {
            $event = DB::table('whatsapp_delivery_events')->where('provider_id', $id)->lockForUpdate()->first();
            if (! $event) {
                return;
            }
            $message = WhatsappMessage::where('provider_id', $id)->where('direction', 'out')->lockForUpdate()->first();
            if (! $message) {
                return;
            } // Provider callback may arrive before the send HTTP response.
            if ((self::RANK[$event->status] ?? 0) >= (self::RANK[$message->status] ?? 0)) {
                $message->update(['status' => $event->status, 'error' => $event->error]);
            }
            DB::table('whatsapp_delivery_events')->where('provider_id', $id)->delete();
        });
    }
}
