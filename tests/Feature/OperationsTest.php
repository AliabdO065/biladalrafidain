<?php

namespace Tests\Feature;

use App\Jobs\SendMerchantStatement;
use App\Models\AccountBatch;
use App\Models\LandingSetting;
use App\Models\LandingShipment;
use App\Models\LandingTrip;
use App\Models\MerchantStatement;
use App\Models\User;
use App\Services\AccountSpreadsheetReader;
use App\Services\WhatsAppAccounts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use ZipArchive;

class OperationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        config(['whatsapp.token' => null]);
    }

    public function test_operations_require_authentication(): void
    {
        foreach (['/admin/trips', '/admin/accounts', '/admin/landing/shipments'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
        $this->post('/admin/trips')->assertRedirect('/login');
        $this->post('/admin/accounts/merchant/' . str_repeat('a', 64) . '/prepare')->assertRedirect('/login');
    }

    public function test_trip_advances_through_five_stages_and_rejects_stale_clicks(): void
    {
        $this->actingAs(User::factory()->create());
        $this->post('/admin/trips', ['reference' => 'TR-1', 'origin' => 'Cairo', 'destination' => 'Baghdad'])->assertSessionHasNoErrors();
        $trip = LandingTrip::firstOrFail();
        $this->assertSame(1, $trip->events()->count());
        foreach (['received', 'customs', 'in_transit', 'out_for_delivery'] as $status) {
            $this->post("/admin/trips/{$trip->id}/advance", ['expected_status' => $status])->assertSessionHasNoErrors();
        }
        $this->assertSame('delivered', $trip->fresh()->status);
        $this->assertSame(5, $trip->events()->count());
        $this->post("/admin/trips/{$trip->id}/advance", ['expected_status' => 'received'])->assertSessionHasErrors('status');
        $this->assertSame(5, $trip->events()->count());
    }

    public function test_stale_click_does_not_skip_a_stage(): void
    {
        $this->actingAs(User::factory()->create());
        $trip = $this->trip();
        $this->post("/admin/trips/{$trip->id}/advance", ['expected_status' => 'received'])->assertSessionHasNoErrors();
        $this->post("/admin/trips/{$trip->id}/advance", ['expected_status' => 'received'])->assertSessionHasErrors('status');
        $this->assertSame('customs', $trip->fresh()->status);
    }

    public function test_shipments_get_random_codes_and_public_tracking_follows_trip(): void
    {
        LandingSetting::create([]);
        $this->actingAs(User::factory()->create());
        $trip = $this->trip();
        $this->post('/admin/landing/shipments/store', ['trip_id' => $trip->id, 'status' => 'received', 'receiver_name' => 'Client'])->assertSessionHasNoErrors();
        $shipment = LandingShipment::firstOrFail();
        $this->assertMatchesRegularExpression('/^BR-[A-Z0-9]{16}$/', $shipment->tracking_code);
        $this->assertSame('Cairo', $shipment->origin);
        $this->post("/admin/trips/{$trip->id}/advance", ['expected_status' => 'received']);
        $this->assertSame('customs', $shipment->fresh()->current_status);
        $this->app['auth']->forgetGuards();
        $this->get('/tracking?code=' . $shipment->tracking_code)->assertOk()->assertViewHas('shipment', fn ($item) => $item->current_status === 'customs');
        $this->get('/tracking?code=WRONG')->assertOk()->assertViewHas('notFound', true);
    }

    public function test_all_dashboard_screens_render(): void
    {
        $this->actingAs(User::factory()->create());
        $trip = $this->trip();
        $this->post('/admin/landing/shipments/store', ['status' => 'received']);
        foreach (['/admin', '/admin/trips', "/admin/trips/{$trip->id}", '/admin/accounts', '/admin/landing/shipments', '/admin/landing/shipments/edit/' . LandingShipment::first()->id, '/admin/landing/settings'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/admin/landing/shipments/add')->assertRedirect(route('dashboard.landing.shipments', ['create' => 1]));
    }

    public function test_xlsx_shared_and_inline_strings_with_sparse_cells_are_read(): void
    {
        $file = $this->xlsx(false);
        try {
            $rows = app(AccountSpreadsheetReader::class)->read($file);
            $this->assertSame('Trader', $rows[0]['name']);
            $this->assertSame('+201012345678', $rows[0]['phone']);
            $this->assertSame('12.50', $rows[0]['amount']);
        } finally {
            unlink($file->getRealPath());
        }
    }

    public function test_xlsx_formulas_are_rejected(): void
    {
        $file = $this->xlsx(true);
        try {
            $this->expectException(ValidationException::class);
            app(AccountSpreadsheetReader::class)->read($file);
        } finally {
            unlink($file->getRealPath());
        }
    }

    public function test_bulk_send_is_disabled_without_whatsapp_credentials(): void
    {
        Queue::fake();
        $this->actingAs(User::factory()->create());
        $this->seedLegacyStatements("name,phone,amount,currency\nOne,+201012345678,10,EGP\n");
        $batch = AccountBatch::firstOrFail();
        $this->post("/admin/accounts/{$batch->id}/send", ['reviewed' => 1])->assertSessionHasErrors('whatsapp');
        $this->assertSame('pending', MerchantStatement::first()->status);
        Queue::assertNothingPushed();
        Http::assertNothingSent();
    }

    public function test_bulk_queue_is_idempotent_and_worker_sends_only_own_template_parameters(): void
    {
        $this->configureWhatsApp();
        Queue::fake();
        $this->actingAs(User::factory()->create());
        $this->seedLegacyStatements("name,phone,amount,currency\nOne,+201012345678,10,EGP\nTwo,+9647701234567,20,USD\n");
        $batch = AccountBatch::firstOrFail();
        $this->post("/admin/accounts/{$batch->id}/send", ['reviewed' => 1])->assertSessionHasNoErrors();
        $this->post("/admin/accounts/{$batch->id}/send", ['reviewed' => 1])->assertSessionHasNoErrors();
        Queue::assertPushed(SendMerchantStatement::class, 2);
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.test']]], 200)]);
        $statement = MerchantStatement::firstOrFail();
        $job = new SendMerchantStatement($statement->id);
        $job->handle(app(WhatsAppAccounts::class));
        $job->handle(app(WhatsAppAccounts::class));
        $this->assertSame('accepted', $statement->fresh()->status);
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request['to'] === '201012345678'
            && $request['template']['components'][0]['parameters'][0]['text'] === 'One'
            && $request['template']['components'][0]['parameters'][2]['text'] === '10.00');
    }

    public function test_timeout_is_uncertain_and_is_not_automatically_retried(): void
    {
        $this->configureWhatsApp();
        $this->actingAs(User::factory()->create());
        $this->seedLegacyStatements("name,phone,amount,currency\nOne,+201012345678,10,EGP\n");
        $statement = MerchantStatement::firstOrFail();
        $statement->update(['status' => 'queued']);
        Http::fake(function () { throw new ConnectionException('timeout'); });
        $job = new SendMerchantStatement($statement->id);
        $job->handle(app(WhatsAppAccounts::class));
        $this->assertSame('unknown', $statement->fresh()->status);
        Queue::fake();
        $this->post("/admin/accounts/{$statement->batch_id}/send", ['reviewed' => 1, 'retry_failed' => 1]);
        Queue::assertNothingPushed();
    }

    public function test_bulk_uses_database_queue_even_when_default_is_sync(): void
    {
        $this->configureWhatsApp();
        $this->actingAs(User::factory()->create());
        config(['queue.default' => 'sync']);
        $this->seedLegacyStatements("name,phone,amount,currency\nOne,+201012345678,10,EGP\n");
        $this->post('/admin/accounts/' . AccountBatch::first()->id . '/send', ['reviewed' => 1])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('jobs', ['queue' => 'whatsapp']);
        $this->assertSame('queued', MerchantStatement::first()->status);
        Http::assertNothingSent();
    }

    public function test_independent_shipment_button_updates_status_and_history(): void
    {
        $this->actingAs(User::factory()->create());
        $this->post('/admin/landing/shipments/store', ['status' => 'received']);
        $shipment = LandingShipment::firstOrFail();
        $this->post("/admin/landing/shipments/{$shipment->id}/advance", ['expected_status' => 'received'])->assertSessionHasNoErrors();
        $this->assertSame('customs', $shipment->fresh()->current_status);
        $this->assertSame(2, $shipment->events()->count());
        $this->post("/admin/landing/shipments/{$shipment->id}/advance", ['expected_status' => 'received'])->assertSessionHasErrors('status');
    }

    public function test_trip_details_can_be_corrected_without_changing_stage(): void
    {
        $this->actingAs(User::factory()->create());
        $trip = $this->trip();
        $this->post("/admin/trips/{$trip->id}/update", ['reference' => $trip->reference, 'origin' => 'Alexandria', 'destination' => 'Basra', 'estimated_delivery' => '2026-11-10'])->assertSessionHasNoErrors();
        $this->assertSame('Alexandria', $trip->fresh()->origin);
        $this->assertSame('received', $trip->fresh()->status);
    }

    private function configureWhatsApp(): void
    {
        config(['whatsapp.token' => 'test-token', 'whatsapp.phone_number_id' => '123456', 'whatsapp.version' => 'v23.0', 'whatsapp.template' => 'account_statement']);
    }

    public function test_trips_datatable_filters_by_stage_and_retains_details_controls(): void
    {
        $this->actingAs(User::factory()->create());
        $trip = $this->trip();
        $other = $this->trip();
        $other->update(['status' => 'delivered']);
        $this->get('/admin/trips')->assertOk()->assertSee('data-table-details="trips-table"', false)->assertSee('data-filter-param="status"', false);
        $this->getJson('/admin/trips/data?status=received')->assertOk()->assertJsonPath('recordsTotal', 1)->assertJsonPath('data.0.reference', $trip->reference);
        $this->getJson('/admin/trips/data?status=delivered')->assertOk()->assertJsonPath('recordsTotal', 1)->assertJsonPath('data.0.reference', $other->reference);
        $this->getJson('/admin/trips/data')->assertOk()->assertJsonPath('recordsTotal', 2);
        $this->getJson('/admin/trips/data?status=invalid')->assertUnprocessable();
    }

    private function trip(): LandingTrip
    {
        return LandingTrip::create(['reference' => 'TR-' . uniqid(), 'origin' => 'Cairo', 'destination' => 'Baghdad', 'status' => 'received']);
    }

    private function seedLegacyStatements(string $contents)
    {
        $batch = AccountBatch::firstOrCreate(['file_hash' => hash('sha256', $contents)], ['title' => 'October', 'created_by' => auth()->id()]);
        if ($batch->wasRecentlyCreated) {
            $lines = preg_split('/\r?\n/', trim($contents));
            $header = str_getcsv(array_shift($lines));
            foreach ($lines as $line) {
                $row = array_combine($header, str_getcsv($line));
                $merchant = \App\Models\Merchant::firstOrCreate(['phone' => $row['phone']], ['name' => $row['name']]);
                $batch->statements()->create($row + ['merchant_id' => $merchant->id]);
            }
        }
        return new \Illuminate\Testing\TestResponse(redirect()->route('dashboard.accounts.show', $batch));
    }

    private function xlsx(bool $formula): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'accounts-test-');
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::OVERWRITE);
        $zip->addFromString('xl/workbook.xml', '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Accounts" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Target="worksheets/sheet1.xml"/></Relationships>');
        $zip->addFromString('xl/sharedStrings.xml', '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><si><t>Trader</t></si></sst>');
        $cells = '';
        foreach (['A' => 'name', 'B' => 'phone', 'C' => 'amount', 'D' => 'currency', 'F' => 'details'] as $column => $label) {
            $cells .= '<c r="' . $column . '1" t="inlineStr"><is><t>' . $label . '</t></is></c>';
        }
        $zip->addFromString('xl/worksheets/sheet1.xml', '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData><row r="1">' . $cells . '</row><row r="2"><c r="A2" t="s"><v>0</v></c><c r="B2" t="inlineStr"><is><t>+201012345678</t></is></c><c r="C2">' . ($formula ? '<f>1+1</f>' : '') . '<v>12.50</v></c><c r="D2" t="inlineStr"><is><t>EGP</t></is></c></row></sheetData></worksheet>');
        $zip->close();

        return new UploadedFile($path, 'accounts.xlsx', null, null, true);
    }
}
