<?php

namespace Tests\Feature;

use App\Jobs\SendMerchantStatement;
use App\Models\AccountBatch;
use App\Models\LandingShipment;
use App\Models\LandingTrip;
use App\Models\MerchantStatement;
use App\Models\User;
use App\Services\ShipmentAccounts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ShipmentAccountsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
        Http::preventStrayRequests();
        config(['whatsapp.token' => null]);
    }

    public function test_accounts_read_shipments_directly_and_never_sum_report_total(): void
    {
        $one = $this->shipment();
        $this->shipment(['shipping_fee' => '60.25', 'paid_amount' => '10.00', 'balance' => null, 'total' => '99999.00']);
        $this->shipment(['merchant_number' => 'M-2', 'receiver_name' => 'Another Trader']);
        $page = $this->get('/admin/accounts')->assertOk()->assertDontSee('type="file"', false);
        $page->assertViewHas('merchants', fn ($rows) => $rows->total() === 2);
        $summary = app(ShipmentAccounts::class)->summary($one->account_key, ['bucket' => 'USD']);
        $this->assertSame(2, $summary['count']);
        $this->assertSame('160.25', $summary['fee']);
        $this->assertSame('40.00', $summary['paid']);
        $this->assertSame('120.25', $summary['balance']);
        $this->assertDatabaseCount('account_batches', 0);
        $this->assertDatabaseCount('merchant_statements', 0);
        $this->get($this->url($one))->assertOk()->assertSee('160.25')->assertDontSee('Another Trader');
    }

    public function test_balance_updates_after_shipment_edits_without_creating_extra_records(): void
    {
        $shipment = $this->shipment();
        $shipment->update(['shipping_fee' => '150.00', 'paid_amount' => '50.00', 'balance' => '100.00']);
        $this->assertSame('100.00', app(ShipmentAccounts::class)->summary($shipment->account_key, ['bucket' => 'USD'])['balance']);
        $this->assertDatabaseCount('account_batches', 0);
        $shipment->update(['receiver_name' => 'Corrected name']);
        $this->assertSame('Corrected name', app(ShipmentAccounts::class)->summary($shipment->account_key, ['bucket' => 'USD'])['name']);
    }

    public function test_filters_and_currency_groups_do_not_mix_amounts(): void
    {
        $trip = LandingTrip::create(['reference' => '180', 'origin' => 'Cairo', 'destination' => 'Baghdad']);
        $one = $this->shipment(['trip_id' => $trip->id, 'receipt_date' => '2026-10-15']);
        $this->shipment(['currency' => 'IQD', 'shipping_fee' => '5000.00', 'paid_amount' => '0.00', 'balance' => '5000.00']);
        $this->shipment(['currency' => null]);
        $this->shipment(['receipt_date' => '2026-09-01']);
        $this->get('/admin/accounts')->assertViewHas('merchants', fn ($rows) => $rows->total() === 3);
        $summary = app(ShipmentAccounts::class)->summary($one->account_key, ['bucket' => 'USD', 'trip_id' => $trip->id, 'from' => '2026-10-01', 'to' => '2026-10-31']);
        $this->assertSame(1, $summary['count']);
        $this->assertSame('70.00', $summary['balance']);
        $this->get('/admin/accounts?to=2026-10-31')->assertSessionHasNoErrors()->assertOk();
    }

    public function test_manual_message_uses_only_this_merchants_account_and_is_idempotent(): void
    {
        $one = $this->shipment();
        $this->shipment(['merchant_number' => 'OTHER', 'receiver_name' => 'Private Other', 'balance' => '888.88']);
        $data = $this->prepareData($one);
        $response = $this->post($this->prepareUrl($one), $data)->assertSessionHasNoErrors()->assertRedirect();
        $target = urldecode($response->headers->get('Location'));
        $this->assertStringContainsString('https://wa.me/9647701234567', $target);
        $this->assertStringContainsString('70.00', $target);
        $this->assertStringNotContainsString('888.88', $target);
        $this->assertStringNotContainsString('Private Other', $target);
        $this->post($this->prepareUrl($one), $data)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('merchant_statements', 1);
        $this->assertDatabaseCount('account_batches', 1);
        $this->assertSame('pending', MerchantStatement::first()->status);
        Http::assertNothingSent();
    }

    public function test_local_number_needs_an_explicit_country_and_currency_is_required(): void
    {
        $one = $this->shipment(['receiver_phone' => '07701234567', 'currency' => null]);
        $data = $this->prepareData($one);
        $data['phone'] = '07701234567';
        $this->post($this->prepareUrl($one), $data)->assertSessionHasErrors('phone');
        $data['country_code'] = '964';
        unset($data['currency']);
        $this->post($this->prepareUrl($one), $data)->assertSessionHasErrors('currency');
        $data['currency'] = 'IQD';
        $this->post($this->prepareUrl($one), $data)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('merchant_statements', ['phone' => '+9647701234567', 'currency' => 'IQD']);
    }

    public function test_changed_or_incomplete_data_is_not_sent_and_snapshots_keep_original_values(): void
    {
        $one = $this->shipment();
        $data = $this->prepareData($one);
        $one->update(['balance' => '40.00']);
        $this->post($this->prepareUrl($one), $data)->assertSessionHasErrors('version');
        $data = $this->prepareData($one);
        $this->post($this->prepareUrl($one), $data)->assertSessionHasNoErrors();
        $one->update(['balance' => '10.00', 'paid_amount' => null]);
        $data = $this->prepareData($one);
        $this->post($this->prepareUrl($one), $data)->assertSessionHasErrors('amounts');
        $this->assertSame('40.00', MerchantStatement::first()->amount);
        $this->assertDatabaseCount('merchant_statements', 1);
    }

    public function test_automatic_send_is_guarded_and_does_not_duplicate_queue_jobs(): void
    {
        $one = $this->shipment();
        $data = $this->prepareData($one) + ['action' => 'queue'];
        $data['action'] = 'queue';
        Queue::fake();
        $this->post($this->prepareUrl($one), $data)->assertSessionHasErrors('whatsapp');
        Queue::assertNothingPushed();
        config(['whatsapp.token' => 'test', 'whatsapp.phone_number_id' => '123', 'whatsapp.version' => 'v23.0', 'whatsapp.template' => 'account_statement', 'whatsapp.language' => 'ar']);
        $this->post($this->prepareUrl($one), $data)->assertSessionHasNoErrors();
        $this->post($this->prepareUrl($one), $data)->assertSessionHasNoErrors();
        Queue::assertPushed(SendMerchantStatement::class, 1);
        $this->assertSame('queued', MerchantStatement::first()->status);
        $this->assertDatabaseCount('merchant_statements', 1);
        Http::assertNothingSent();
    }

    public function test_currency_changes_are_rejected_and_old_excel_endpoint_is_gone(): void
    {
        $one = $this->shipment();
        $data = $this->prepareData($one);
        $data['currency'] = 'EGP';
        $this->post($this->prepareUrl($one), $data)->assertSessionHasErrors('currency');
        $this->post('/admin/accounts/import')->assertStatus(405);
        $this->get('/admin/accounts/template')->assertNotFound();
        $this->get('/admin/accounts/history')->assertOk();
    }

    public function test_account_datatable_searches_and_sorts_all_merchants_on_the_server(): void
    {
        $trip = LandingTrip::create(['reference' => 'TABLE-180', 'origin' => 'Cairo', 'destination' => 'Baghdad']);
        for ($i = 1; $i <= 30; $i++) {
            $this->shipment(['merchant_number' => 'TABLE-' . $i, 'receiver_name' => 'Table Merchant ' . $i,
                'balance' => (string) $i, 'trip_id' => $trip->id, 'receipt_date' => '2026-10-15']);
        }
        $this->shipment(['merchant_number' => 'OTHER', 'receiver_name' => 'Outside trip']);
        $this->getJson('/admin/accounts/data?trip_id=' . $trip->id . '&start=25&length=10')->assertOk()
            ->assertJsonPath('recordsTotal', 30)->assertJsonCount(5, 'data');
        $query = http_build_query(['trip_id' => $trip->id, 'search' => ['value' => 'Table Merchant 30']]);
        $this->getJson('/admin/accounts/data?' . $query)->assertOk()->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.receiver_name', 'Table Merchant 30');
        $query = http_build_query(['trip_id' => $trip->id, 'order' => [['column' => 6, 'dir' => 'asc']]]);
        $this->getJson('/admin/accounts/data?' . $query)->assertOk()->assertJsonPath('data.0.receiver_name', 'Table Merchant 1');
        $this->getJson('/admin/accounts/data?from=2026-11-01&trip_id=' . $trip->id)->assertOk()->assertJsonPath('recordsTotal', 0);
        $this->get('/admin/accounts')->assertOk()->assertSee('data-operations-table', false)->assertSee('accounts-table', false);
        $this->app['auth']->forgetGuards();
        $this->getJson('/admin/accounts/data')->assertUnauthorized();
    }

    private function shipment(array $overrides = []): LandingShipment
    {
        return LandingShipment::create(array_replace([
            'tracking_code' => 'BR-' . uniqid(), 'receiver_name' => 'Trader One', 'receiver_phone' => '+9647701234567',
            'merchant_number' => 'M-1', 'status' => 'received', 'currency' => 'USD',
            'shipping_fee' => '100.00', 'paid_amount' => '30.00', 'balance' => '70.00', 'total' => '9999.99',
        ], $overrides));
    }

    private function prepareData(LandingShipment $shipment): array
    {
        $summary = app(ShipmentAccounts::class)->summary($shipment->account_key, ['bucket' => $shipment->currency]);

        return ['bucket' => $shipment->currency, 'version' => $summary['version'], 'reviewed' => 1,
            'phone' => '+9647701234567', 'currency' => $shipment->currency ?: 'USD', 'action' => 'manual'];
    }

    private function url(LandingShipment $shipment): string
    {
        return '/admin/accounts/merchant/' . $shipment->account_key . '?' . http_build_query(['bucket' => $shipment->currency]);
    }

    private function prepareUrl(LandingShipment $shipment): string
    {
        return '/admin/accounts/merchant/' . $shipment->account_key . '/prepare';
    }
}
