<?php

namespace Tests\Feature;

use App\Jobs\SendOperationNotification;
use App\Models\LandingShipment;
use App\Models\LandingTrip;
use App\Models\NotificationTemplate;
use App\Models\TradeImport;
use App\Models\TradeVoucher;
use App\Models\TradingContact;
use App\Models\User;
use App\Models\WhatsappMessage;
use App\Services\OperationNotifications;
use App\Services\ShipmentDocument;
use App\Services\VoucherSpreadsheetImporter;
use App\Services\WhatsAppMessaging;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TradeNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_trip_documents_can_be_prepared_without_duplicate_notifications(): void
    {
        $trip = LandingTrip::create(['reference' => 'EXISTING-TRIP', 'origin' => 'Egypt', 'destination' => 'Iraq', 'status' => 'received']);
        foreach ([1, 2] as $number) {
            LandingShipment::create(['tracking_code' => 'EXISTING-'.$number, 'trip_id' => $trip->id, 'merchant_number' => 'M-'.$number, 'receiver_name' => 'Merchant '.$number]);
        }
        LandingShipment::create(['tracking_code' => 'NO-MERCHANT', 'trip_id' => $trip->id]);
        $this->post('/admin/trips/'.$trip->id.'/documents/prepare')->assertRedirect('/login');
        $this->actingAs(User::factory()->create());
        $this->post('/admin/trips/'.$trip->id.'/documents/prepare')->assertRedirect(route('dashboard.trade.messages', ['trip_id' => $trip->id]));
        $this->post('/admin/trips/'.$trip->id.'/documents/prepare')->assertRedirect(route('dashboard.trade.messages', ['trip_id' => $trip->id]));
        $this->assertDatabaseCount('whatsapp_messages', 2);
        $this->assertSame(['draft'], WhatsappMessage::distinct()->pluck('status')->all());
    }

    private function file(string $source, array $data): UploadedFile
    {
        $headers = $source === 'payments' ? ['م', 'رقم السند', 'رقم السند المخصص', 'التاريخ', 'نوع العملية', 'المصانع / الشركات', 'رقم التاجر', 'المستفيد / التاجر', 'المبلغ', 'البيان', 'رقم الرحلة']
            : ['م', 'رقم السند', 'رقم السند المخصص', 'التاريخ', 'نوع العملية', 'رقم التاجر', 'المستفيد / التاجر', 'رقم الحساب', 'المصانع / الشركات', 'المبلغ', 'البيان', 'رقم الرحلة'];
        $rows = array_merge([['Report'], $headers], $data);
        $path = tempnam(sys_get_temp_dir(), 'voucher');
        $zip = new \ZipArchive;
        $zip->open($path, \ZipArchive::OVERWRITE);
        $zip->addFromString('xl/workbook.xml', '<workbook xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<Relationships><Relationship Id="rId1" Target="worksheets/sheet1.xml"/></Relationships>');
        $xml = '<worksheet><sheetData>';
        foreach ($rows as $index => $cells) {
            $xml .= '<row r="'.($index + 1).'">';
            foreach ($cells as $col => $value) {
                $xml .= '<c r="'.chr(65 + $col).($index + 1).'" t="inlineStr"><is><t>'.htmlspecialchars($value, ENT_XML1).'</t></is></c>';
            }
            $xml .= '</row>';
        }
        $zip->addFromString('xl/worksheets/sheet1.xml', $xml.'</sheetData></worksheet>');
        $zip->close();
        $this->beforeApplicationDestroyed(fn () => @unlink($path));

        return new UploadedFile($path, 'report.xlsx', null, null, true);
    }

    private function payment(string $reference = 'PAY-1', string $amount = '3530', string $type = 'مـشتريات'): array
    {
        return ['1', $reference, 'CUSTOM-1', '2026/07/30', $type, 'Factory A', 'M-256', 'Test Merchant', $amount, 'Paid supplier', '155'];
    }

    private function enabled(): void
    {
        config(['whatsapp.token' => 'test', 'whatsapp.phone_number_id' => '123456', 'whatsapp.version' => 'v23.0', 'whatsapp.business_id' => '98765']);
    }

    public function test_preview_excludes_expenses_and_confirmation_is_idempotent_without_original_storage(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $file = $this->file('payments', [$this->payment(), $this->payment('EXP-1', '100', 'صرفيات جمرك مصر')]);
        $this->post('/admin/imports/preview', ['file' => $file, 'source' => 'payments', 'currency' => 'USD'])->assertRedirect();
        $import = TradeImport::firstOrFail();
        $this->assertSame(1, $import->excluded);
        $this->assertCount(1, $import->payload);
        $this->assertDatabaseCount('trade_vouchers', 0);
        $this->post('/admin/imports/'.$import->id.'/confirm')->assertRedirect()->assertSessionHasNoErrors();
        $this->post('/admin/imports/'.$import->id.'/confirm')->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseCount('trade_vouchers', 1);
        $this->assertDatabaseCount('whatsapp_messages', 1);
        $this->assertNull($import->fresh()->payload);
        $message = WhatsappMessage::first();
        $this->assertSame('draft', $message->status);
        $this->assertStringContainsString('نيابةً عنكم', $message->body);
        $this->assertStringNotContainsString('شراء جديدة', $message->body);
        $this->assertStringNotContainsString('الرصيد المتبقي', $message->body);
    }

    public function test_changed_voucher_blocks_the_whole_confirmation(): void
    {
        $user = User::factory()->create();
        $service = app(VoucherSpreadsheetImporter::class);
        $one = $service->preview($this->file('payments', [$this->payment()]), 'payments', 'USD', $user->id);
        $service->confirm($one);
        $two = $service->preview($this->file('payments', [$this->payment('PAY-NEW'), $this->payment('PAY-1', '999')]), 'payments', 'USD', $user->id);
        try {
            $service->confirm($two);
            $this->fail('Changed voucher should fail.');
        } catch (ValidationException $exception) {
        }
        $this->assertDatabaseCount('trade_vouchers', 1);
        $this->assertDatabaseCount('whatsapp_messages', 1);
        $this->assertNull($two->fresh()->confirmed_at);
    }

    public function test_receipts_keep_shipping_separate_without_a_guessed_balance(): void
    {
        $user = User::factory()->create();
        $service = app(VoucherSpreadsheetImporter::class);
        $base = ['1', 'REC-1', 'C1', '2026/07/30', 'تسديد لحساب المشتريات', 'M-1', 'Test Merchant', 'M-1', '***', '3000', 'Receipt', '154'];
        $second = $base;
        $second[1] = 'REC-2';
        $second[4] = 'تسديد نقل';
        $import = $service->preview($this->file('receipts', [$base, $second]), 'receipts', 'USD', $user->id);
        $service->confirm($import);
        $this->assertSame(['purchase_receipt', 'shipping_receipt'], TradeVoucher::orderBy('id')->pluck('kind')->all());
        $this->assertStringContainsString('المشتريات', WhatsappMessage::first()->body);
        $this->assertStringContainsString('الشحن', WhatsappMessage::orderByDesc('id')->first()->body);
        $this->assertStringNotContainsString('الرصيد المتبقي', WhatsappMessage::first()->body);
    }

    public function test_auto_send_requires_verified_template_and_reviewed_recipient_and_does_not_duplicate_jobs(): void
    {
        Bus::fake();
        $this->enabled();
        $contact = TradingContact::create(['number' => 'M-256', 'name' => 'Test Merchant', 'phone' => '+9647701234567', 'opt_in' => true]);
        $template = NotificationTemplate::forKind('supplier_payment');
        $template->update(['provider_name' => 'supplier_payment', 'auto_send' => true]);
        $template->update(['verified_hash' => $template->contentHash()]);
        $user = User::factory()->create();
        $service = app(VoucherSpreadsheetImporter::class);
        $import = $service->preview($this->file('payments', [$this->payment()]), 'payments', 'USD', $user->id);
        $service->confirm($import);
        $message = WhatsappMessage::firstOrFail();
        $this->assertSame('queued', $message->status);
        $service->confirm($import);
        app(OperationNotifications::class)->queue($message);
        Bus::assertDispatchedTimes(SendOperationNotification::class, 1);
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.TEST']]])]);
        (new SendOperationNotification($message->id))->handle(app(WhatsAppMessaging::class));
        (new SendOperationNotification($message->id))->handle(app(WhatsAppMessaging::class));
        Http::assertSentCount(1);
        $this->assertSame('accepted', $message->fresh()->status);
    }

    public function test_template_verification_checks_actual_provider_body_and_edits_disable_it(): void
    {
        $this->enabled();
        $user = User::factory()->create();
        $this->actingAs($user);
        $template = NotificationTemplate::forKind('receipt');
        $template->update(['provider_name' => 'receipt_v1']);
        Http::fake(['graph.facebook.com/*' => Http::response(['data' => [['name' => 'receipt_v1', 'status' => 'APPROVED', 'language' => 'ar', 'components' => [['type' => 'BODY', 'text' => $template->providerBody()]]]]])]);
        $this->post('/admin/whatsapp/templates/'.$template->id.'/verify')->assertRedirect()->assertSessionHasNoErrors();
        $this->assertTrue($template->fresh()->isVerified());
        $this->post('/admin/whatsapp/templates/'.$template->id, ['body' => $template->body.' changed', 'provider_name' => 'receipt_v1', 'language' => 'ar'])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertFalse($template->fresh()->isVerified());
        $this->post('/admin/whatsapp/templates/'.$template->id, ['body' => $template->fresh()->body, 'provider_name' => 'receipt_v1', 'language' => 'ar', 'auto_send' => 1])->assertSessionHasErrors();
    }

    public function test_webhook_signatures_deduplication_and_service_window(): void
    {
        config(['whatsapp.app_secret' => 'secret', 'whatsapp.phone_number_id' => '123']);
        $payload = ['entry' => [['changes' => [['value' => ['metadata' => ['phone_number_id' => '123'], 'messages' => [['from' => '9647701234567', 'id' => 'wamid.IN', 'type' => 'text', 'text' => ['body' => 'Hello'], 'timestamp' => (string) now()->timestamp]]]]]]]];
        $json = json_encode($payload);
        $this->call('POST', '/whatsapp/webhook', [], [], [], ['CONTENT_TYPE' => 'application/json'], $json)->assertForbidden();
        $headers = ['CONTENT_TYPE' => 'application/json', 'HTTP_X_HUB_SIGNATURE_256' => 'sha256='.hash_hmac('sha256', $json, 'secret')];
        $this->call('POST', '/whatsapp/webhook', [], [], [], $headers, $json)->assertOk();
        $this->call('POST', '/whatsapp/webhook', [], [], [], $headers, $json)->assertOk();
        $this->assertDatabaseCount('whatsapp_messages', 1);
        $this->assertTrue(app(WhatsAppMessaging::class)->canReply('+9647701234567'));
        $this->travel(25)->hours();
        $this->assertFalse(app(WhatsAppMessaging::class)->canReply('+9647701234567'));
    }

    public function test_delivery_events_never_regress_from_read_to_delivered(): void
    {
        config(['whatsapp.app_secret' => 'secret', 'whatsapp.phone_number_id' => '123']);
        $message = WhatsappMessage::create(['kind' => 'reply', 'phone' => '+9647701234567', 'status' => 'accepted', 'provider_id' => 'wamid.OUT']);
        foreach (['read', 'delivered', 'failed'] as $state) {
            $json = json_encode(['entry' => [['changes' => [['value' => ['metadata' => ['phone_number_id' => '123'], 'statuses' => [['id' => 'wamid.OUT', 'status' => $state]]]]]]]]);
            $this->call('POST', '/whatsapp/webhook', [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_X_HUB_SIGNATURE_256' => 'sha256='.hash_hmac('sha256', $json, 'secret')], $json)->assertOk();
        }
        $this->assertSame('read', $message->fresh()->status);
    }

    public function test_new_pages_and_private_documents_require_login_and_render_in_both_languages(): void
    {
        $shipment = LandingShipment::create(['tracking_code' => 'PRIVATE-BILL', 'merchant_number' => 'M-1', 'receiver_name' => 'Test Merchant', 'balance' => null]);
        foreach (['/admin/imports', '/admin/merchants', '/admin/whatsapp/templates', '/admin/whatsapp/messages', '/admin/shipments/'.$shipment->id.'/document'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
        $this->actingAs(User::factory()->create());
        foreach (['ar', 'en'] as $locale) {
            foreach (['/admin/imports', '/admin/merchants', '/admin/whatsapp/templates', '/admin/whatsapp/messages', '/admin/shipments/'.$shipment->id.'/document'] as $url) {
                $this->withSession(['admin_locale' => $locale])->get($url)->assertOk()->assertDontSee('WHATSAPP_ACCESS_TOKEN');
            }
        }
        $this->get('/admin/shipments/'.$shipment->id.'/document')->assertHeaderContains('Cache-Control', 'no-store')->assertHeaderContains('Cache-Control', 'private')->assertDontSee('USD');
    }

    public function test_retention_preserves_vouchers_ids_and_queued_messages(): void
    {
        $user = User::factory()->create();
        $service = app(VoucherSpreadsheetImporter::class);
        $import = $service->preview($this->file('payments', [$this->payment()]), 'payments', 'USD', $user->id);
        $service->confirm($import);
        $message = WhatsappMessage::first();
        $message->update(['status' => 'read', 'provider_id' => 'wamid.KEEP']);
        WhatsappMessage::whereKey($message->id)->update(['updated_at' => now()->subDays(100)]);
        $queued = WhatsappMessage::create(['kind' => 'reply', 'phone' => '+9647701234567', 'body' => 'Do not prune', 'status' => 'queued']);
        WhatsappMessage::whereKey($queued->id)->update(['updated_at' => now()->subDays(100)]);
        $this->artisan('notifications:prune')->assertExitCode(0);
        $this->assertNull($message->fresh()->body);
        $this->assertSame('wamid.KEEP', $message->fresh()->provider_id);
        $this->assertSame('Do not prune', $queued->fresh()->body);
        $this->assertDatabaseCount('trade_vouchers', 1);
    }

    public function test_supplied_vouchers_when_available(): void
    {
        $directory = getenv('TEST_VOUCHER_DIRECTORY');
        if (! $directory) {
            $this->markTestSkipped('Set TEST_VOUCHER_DIRECTORY to verify the supplied reports.');
        }
        $user = User::factory()->create();
        $service = app(VoucherSpreadsheetImporter::class);
        foreach (['payments' => ['VoucherPayments_20261026012125.xlsx', 5, 2], 'receipts' => ['VoucherReceipts_20261026012119.xlsx', 4, 0]] as $source => $spec) {
            $file = new UploadedFile($directory.DIRECTORY_SEPARATOR.$spec[0], $spec[0], null, null, true);
            $import = $service->preview($file, $source, 'USD', $user->id);
            $this->assertCount($spec[1], $import->payload);
            $this->assertSame($spec[2], $import->excluded);
            $service->confirm($import);
        }
        $this->assertDatabaseCount('trade_vouchers', 9);
        $this->assertDatabaseCount('whatsapp_messages', 9);
        $this->assertDatabaseCount('jobs', 0);
    }

    public function test_image_notifications_are_private_source_checked_and_temporary(): void
    {
        $this->enabled();
        $template = NotificationTemplate::forKind('shipment');
        $template->update(['provider_name' => 'shipment_doc']);
        $template->update(['verified_hash' => $template->contentHash()]);
        $shipment = LandingShipment::create(['tracking_code' => 'ONLY-THIS-SHIPMENT', 'merchant_number' => 'M-IMAGE', 'receiver_name' => 'Image Merchant', 'receiver_phone' => '+9647701234567', 'packages_count' => 2, 'weight_kg' => '77.000', 'shipping_fee' => '61.60', 'paid_amount' => 0, 'balance' => '61.60', 'currency' => 'USD', 'status' => 'received']);
        $contact = TradingContact::where('number', 'M-IMAGE')->firstOrFail();
        $contact->update(['opt_in' => true]);
        $message = app(OperationNotifications::class)->shipment($shipment);
        $folder = storage_path('framework/testing/image-'.uniqid());
        mkdir($folder, 0755, true);
        $png = $folder.'/image.png';
        file_put_contents($png, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jp2kAAAAASUVORK5CYII='));
        $this->mock(ShipmentDocument::class, function ($mock) use ($folder, $png) {
            $mock->shouldReceive('render')->once()->andReturn(['folder' => $folder, 'path' => $png]);
        });
        Http::fake(['*/media' => Http::response(['id' => 'media-private']), '*/messages' => Http::response(['messages' => [['id' => 'wamid.IMAGE']]])]);
        $result = app(WhatsAppMessaging::class)->send($message);
        $this->assertSame('accepted', $result['status']);
        $this->assertDirectoryDoesNotExist($folder);
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/messages') && $request['to'] === '9647701234567' && $request['template']['components'][1]['parameters'][0]['image']['id'] === 'media-private');
        $shipment->update(['shipping_fee' => '99.00']);
        $this->assertSame('failed', app(WhatsAppMessaging::class)->send($message)['status']);
        Http::assertSentCount(2);
        app(OperationNotifications::class)->refresh($message);
        $this->assertSame(OperationNotifications::shipmentVersion($shipment), $message->fresh()->source_version);
        $message->update(['status' => 'accepted']);
        try {
            app(OperationNotifications::class)->refresh($message);
            $this->fail('Cannot replace accepted message.');
        } catch (ValidationException $exception) {
        }
    }

    public function test_reply_is_idempotent_and_checked_again_when_the_worker_runs(): void
    {
        $this->enabled();
        Bus::fake();
        $this->actingAs(User::factory()->create());
        WhatsappMessage::create(['phone' => '+9647701234567', 'direction' => 'in', 'kind' => 'incoming', 'body' => 'Hello', 'status' => 'received', 'provider_at' => now()]);
        $data = ['phone' => '+9647701234567', 'body' => 'Reply text', 'nonce' => (string) \Illuminate\Support\Str::uuid()];
        $this->post('/admin/whatsapp/reply', $data)->assertRedirect()->assertSessionHasNoErrors();
        $this->post('/admin/whatsapp/reply', $data)->assertRedirect()->assertSessionHasNoErrors();
        Bus::assertDispatchedTimes(SendOperationNotification::class, 1);
        $this->assertDatabaseCount('whatsapp_messages', 2);
        Http::fake();
        $this->travel(25)->hours();
        $reply = WhatsappMessage::where('kind', 'reply')->firstOrFail();
        (new SendOperationNotification($reply->id))->handle(app(WhatsAppMessaging::class));
        $this->assertSame('failed', $reply->fresh()->status);
        Http::assertNothingSent();
    }

    public function test_local_numbers_need_country_and_unchecking_consent_is_supported(): void
    {
        $this->actingAs(User::factory()->create());
        $contact = TradingContact::create(['number' => 'LOCAL-1', 'name' => 'Local Merchant']);
        $this->post('/admin/merchants/'.$contact->id, ['name' => $contact->name, 'phone' => '07701234567', 'opt_in' => 1])->assertSessionHasErrors('phone');
        $this->post('/admin/merchants/'.$contact->id, ['name' => $contact->name, 'phone' => '07701234567', 'country' => '964', 'opt_in' => 1])->assertRedirect();
        $this->assertSame('+9647701234567', $contact->fresh()->phone);
        $this->assertTrue($contact->fresh()->opt_in);
        $this->post('/admin/merchants/'.$contact->id, ['name' => $contact->name, 'phone' => $contact->fresh()->phone])->assertRedirect();
        $this->assertFalse($contact->fresh()->opt_in);
    }

    public function test_early_provider_status_is_reconciled_after_http_send_response(): void
    {
        $this->enabled();
        WhatsappMessage::create(['phone' => '+9647701234567', 'direction' => 'in', 'kind' => 'incoming', 'status' => 'received', 'provider_at' => now()]);
        $message = WhatsappMessage::create(['phone' => '+9647701234567', 'direction' => 'out', 'kind' => 'reply', 'status' => 'queued', 'body' => 'Reply']);
        Http::fake(function () {
            app(\App\Services\WhatsAppDelivery::class)->record('wamid.EARLY', 'delivered', null);

            return Http::response(['messages' => [['id' => 'wamid.EARLY']]]);
        });
        (new SendOperationNotification($message->id))->handle(app(WhatsAppMessaging::class));
        $this->assertSame('delivered', $message->fresh()->status);
        $this->assertDatabaseCount('whatsapp_delivery_events', 0);
    }

    public function test_webhook_challenge_and_merchant_movements_are_available(): void
    {
        config(['whatsapp.verify_token' => 'test-verify']);
        $this->get('/whatsapp/webhook?hub.mode=subscribe&hub.verify_token=wrong&hub.challenge=123')->assertForbidden();
        $this->get('/whatsapp/webhook?hub.mode=subscribe&hub.verify_token=test-verify&hub.challenge=123')->assertOk()->assertSee('123');
        $contact = TradingContact::create(['number' => 'M-LEDGER', 'name' => 'Ledger Merchant']);
        $this->get('/admin/merchants/'.$contact->id.'/movements')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/admin/merchants/'.$contact->id.'/movements')->assertOk()->assertSee($contact->name);
    }

    public function test_current_supplied_shipment_report_excludes_summary_rows(): void
    {
        $directory = getenv('TEST_VOUCHER_DIRECTORY');
        if (! $directory) {
            $this->markTestSkipped('Set TEST_VOUCHER_DIRECTORY to verify the current shipment report.');
        }
        $path = $directory.'/تقارير سندات استلام البضائع من المصانع والشركات  Infosas ERP.xlsx';
        $file = new UploadedFile($path, 'shipments.xlsx', null, null, true);
        $user = User::factory()->create();
        $import = app(\App\Services\ShipmentSpreadsheetImporter::class)->import($file, ['origin' => 'Egypt', 'destination' => 'Iraq', 'currency' => 'USD'], $user->id);
        $this->assertSame(2, $import->shipments_created);
        $this->assertSame(9, (int) LandingShipment::sum('packages_count'));
        $this->assertSame('264.45', (string) LandingShipment::sum('shipping_fee'));
        $this->assertDatabaseCount('whatsapp_messages', 2);
        $this->assertSame(['draft'], WhatsappMessage::distinct()->pluck('status')->all());
    }
}
