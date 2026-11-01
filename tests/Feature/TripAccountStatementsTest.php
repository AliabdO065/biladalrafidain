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

class TripAccountStatementsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
        config(['whatsapp.token' => null]);
        Http::preventStrayRequests();
    }

    public function test_all_merchants_are_prepared_with_only_the_selected_trips_amounts(): void
    {
        $trip = $this->trip();
        $other = $this->trip();
        $this->shipment($trip, 'A');
        $this->shipment($trip, 'A', ['shipping_fee' => '50.00', 'paid_amount' => '10.00', 'balance' => '40.00']);
        $this->shipment($trip, 'B', ['receiver_phone' => '+201012345678']);
        $this->shipment($other, 'A', ['balance' => '999.99']);
        $this->get('/admin/accounts/trips/' . $trip->id)->assertOk()->assertViewHas('groups', fn ($rows) => count($rows) === 2);
        $data = $this->payload($trip);
        $this->post($this->prepareUrl($trip), $data)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('account_batches', 1);
        $this->assertDatabaseCount('merchant_statements', 2);
        $a = MerchantStatement::where('name', 'Trader A')->firstOrFail();
        $this->assertSame('110.00', $a->amount);
        $this->assertSame('150.00', $a->shipping_fee);
        $this->assertSame('40.00', $a->paid_amount);
        $this->assertSame(2, $a->shipment_count);
        $this->assertStringNotContainsString('999.99', $a->messageText());
        $this->assertStringNotContainsString('Trader B', $a->messageText());
        $this->get('/admin/accounts/' . $a->batch_id)->assertOk()->assertSee('wa.me/');
        $this->post($this->prepareUrl($trip), $data)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('account_batches', 1);
        $this->assertDatabaseCount('merchant_statements', 2);
        Http::assertNothingSent();
    }

    public function test_all_groups_are_included_beyond_the_live_account_list_page_size(): void
    {
        $trip = $this->trip();
        for ($i = 1; $i <= 25; $i++) {
            $this->shipment($trip, 'Trader-' . $i);
        }
        $payload = $this->payload($trip);
        $payload['recipients'] = json_encode($payload['recipients']);
        $this->post($this->prepareUrl($trip), $payload)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('merchant_statements', 25);
    }

    public function test_missing_or_invalid_recipients_reject_the_whole_batch(): void
    {
        $trip = $this->trip();
        $this->shipment($trip, 'A');
        $this->shipment($trip, 'B', ['receiver_phone' => '07701234567']);
        $data = $this->payload($trip);
        $this->post($this->prepareUrl($trip), $data)->assertSessionHasErrors('trip_accounts');
        $this->assertDatabaseCount('account_batches', 0);
        $data['default_country_code'] = '964';
        $this->post($this->prepareUrl($trip), $data)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('merchant_statements', ['name' => 'Trader B', 'phone' => '+9647701234567']);
        $trip2 = $this->trip();
        $this->shipment($trip2, 'C');
        $this->shipment($trip2, 'D');
        $partial = $this->payload($trip2);
        array_pop($partial['recipients']);
        $this->post($this->prepareUrl($trip2), $partial)->assertSessionHasErrors('trip_accounts');
        $this->assertDatabaseCount('account_batches', 1);
    }

    public function test_multiple_currencies_can_share_a_phone_and_explicit_unknown_currency_can_merge(): void
    {
        $trip = $this->trip();
        $this->shipment($trip, 'A');
        $this->shipment($trip, 'A', ['currency' => 'IQD', 'balance' => '1000.00']);
        $this->shipment($trip, 'A', ['currency' => null, 'balance' => '5.00']);
        $data = $this->payload($trip);
        $this->post($this->prepareUrl($trip), $data)->assertSessionHasErrors('trip_accounts');
        $data['default_currency'] = 'USD';
        $this->post($this->prepareUrl($trip), $data)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('merchant_statements', 2);
        $this->assertDatabaseHas('merchant_statements', ['currency' => 'USD', 'amount' => '75.00', 'shipment_count' => 2]);
        $this->assertDatabaseHas('merchant_statements', ['currency' => 'IQD', 'amount' => '1000.00', 'shipment_count' => 1]);
    }

    public function test_stale_review_or_stale_prepared_batch_cannot_be_sent(): void
    {
        $trip = $this->trip();
        $shipment = $this->shipment($trip, 'A');
        $data = $this->payload($trip);
        $shipment->update(['balance' => '5.00']);
        $this->post($this->prepareUrl($trip), $data)->assertSessionHasErrors('trip_accounts');
        $this->post($this->prepareUrl($trip), $this->payload($trip))->assertSessionHasNoErrors();
        $batch = AccountBatch::firstOrFail();
        $this->shipment($trip, 'B');
        $this->configureService();
        Queue::fake();
        $this->post('/admin/accounts/' . $batch->id . '/send', ['reviewed' => 1])->assertSessionHasErrors('trip_accounts');
        Queue::assertNothingPushed();
    }

    public function test_bulk_sending_requires_service_and_queues_each_recipient_only_once(): void
    {
        $trip = $this->trip();
        $this->shipment($trip, 'A');
        $this->shipment($trip, 'B');
        $this->post($this->prepareUrl($trip), $this->payload($trip))->assertSessionHasNoErrors();
        $batch = AccountBatch::firstOrFail();
        Queue::fake();
        $this->post('/admin/accounts/' . $batch->id . '/send', ['reviewed' => 1])->assertSessionHasErrors('whatsapp');
        Queue::assertNothingPushed();
        $this->configureService();
        $this->post('/admin/accounts/' . $batch->id . '/send', ['reviewed' => 1])->assertSessionHasNoErrors();
        $this->post('/admin/accounts/' . $batch->id . '/send', ['reviewed' => 1])->assertSessionHasNoErrors();
        Queue::assertPushed(SendMerchantStatement::class, 2);
        Http::assertNothingSent();
    }

    public function test_previously_handled_account_is_not_queued_again_in_trip_batch(): void
    {
        $trip = $this->trip();
        $shipment = $this->shipment($trip, 'A');
        $summary = app(ShipmentAccounts::class)->summary($shipment->account_key, ['bucket' => 'USD', 'trip_id' => $trip->id]);
        $this->post('/admin/accounts/merchant/' . $shipment->account_key . '/prepare', [
            'bucket' => 'USD', 'trip_id' => $trip->id, 'version' => $summary['version'], 'reviewed' => 1,
            'phone' => $shipment->receiver_phone, 'currency' => 'USD', 'action' => 'manual',
        ])->assertSessionHasNoErrors();
        MerchantStatement::first()->update(['status' => 'accepted']);
        $this->post($this->prepareUrl($trip), $this->payload($trip))->assertSessionHasNoErrors();
        $batch = AccountBatch::where('source', 'trip_shipments')->firstOrFail();
        $this->configureService();
        Queue::fake();
        $this->post('/admin/accounts/' . $batch->id . '/send', ['reviewed' => 1])->assertSessionHasNoErrors();
        Queue::assertNothingPushed();
        $this->assertSame('duplicate', $batch->statements()->first()->status);
    }

    private function trip(): LandingTrip
    {
        return LandingTrip::create(['reference' => uniqid('TR-'), 'origin' => 'Cairo', 'destination' => 'Baghdad']);
    }

    private function shipment(LandingTrip $trip, string $merchant, array $overrides = []): LandingShipment
    {
        return LandingShipment::create(array_replace(['trip_id' => $trip->id, 'tracking_code' => uniqid('BR-'), 'receiver_name' => 'Trader ' . $merchant,
            'receiver_phone' => '+9647701234567', 'merchant_number' => $merchant, 'currency' => 'USD',
            'shipping_fee' => '100.00', 'paid_amount' => '30.00', 'balance' => '70.00'], $overrides));
    }

    private function payload(LandingTrip $trip): array
    {
        $accounts = app(ShipmentAccounts::class);
        $groups = $accounts->tripSummaries($trip->id);
        $recipients = [];
        foreach ($groups as $slot => $group) {
            $recipients[$slot] = ['phone' => $group['phone'], 'currency' => $group['currency']];
        }

        return ['version' => $accounts->tripVersion($groups), 'reviewed' => 1, 'recipients' => $recipients];
    }

    private function prepareUrl(LandingTrip $trip): string
    {
        return '/admin/accounts/trips/' . $trip->id . '/prepare';
    }

    private function configureService(): void
    {
        config(['whatsapp.token' => 'test', 'whatsapp.phone_number_id' => '123', 'whatsapp.version' => 'v23.0', 'whatsapp.template' => 'accounts', 'whatsapp.language' => 'ar']);
    }
}
