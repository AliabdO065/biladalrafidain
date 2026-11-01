<?php
namespace Tests\Feature;

use App\Models\{LandingShipment, LandingTrip, ShipmentImport, TradeImport, TradeVoucher, TradingContact, User, WhatsappMessage};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function trip(string $reference = 'FLOW-1', string $status = 'in_transit'): LandingTrip
    {
        return LandingTrip::create(['reference'=>$reference, 'origin'=>'Egypt', 'destination'=>'Iraq', 'status'=>$status]);
    }

    private function shipment(?LandingTrip $trip = null): LandingShipment
    {
        return LandingShipment::create(['tracking_code'=>'FLOW-'.uniqid(), 'trip_id'=>$trip?->id, 'status'=>'received', 'receiver_name'=>'Flow merchant', 'merchant_number'=>'M-FLOW', 'shipping_fee'=>'100.00', 'paid_amount'=>'20.00', 'currency'=>'USD']);
    }

    public function test_detaching_retains_actual_trip_stage_and_moving_follows_the_new_trip(): void
    {
        $this->actingAs(User::factory()->create());
        $trip=$this->trip(); $shipment=$this->shipment($trip);
        $payload=['tracking_code'=>$shipment->tracking_code, 'status'=>'received', 'trip_id'=>''];
        $this->post(route('dashboard.landing.shipments.update',$shipment),$payload)->assertSessionHasNoErrors();
        $this->assertNull($shipment->fresh()->trip_id);
        $this->assertSame('in_transit',$shipment->fresh()->status);
        $this->assertSame('in_transit',$shipment->events()->reorder()->latest('id')->first()->status);
        $next=$this->trip('FLOW-2','delivered');
        $payload['trip_id']=$next->id;
        $this->post(route('dashboard.landing.shipments.update',$shipment),$payload)->assertSessionHasNoErrors();
        $this->assertSame('delivered',$shipment->fresh()->current_status);
        $this->assertSame('delivered',$shipment->fresh()->status);
        $this->assertSame('delivered',$shipment->events()->reorder()->latest('id')->first()->status);
    }

    public function test_creating_inside_a_trip_uses_its_current_stage_and_returns_to_that_trip_filter(): void
    {
        $this->actingAs(User::factory()->create()); $trip=$this->trip();
        $this->post(route('dashboard.landing.shipments.store'),['trip_id'=>$trip->id,'status'=>'received','receiver_name'=>'Merchant'])
            ->assertRedirect(route('dashboard.landing.shipments',['trip_id'=>$trip->id]));
        $shipment=LandingShipment::firstOrFail();
        $this->assertSame('in_transit',$shipment->status);
        $this->assertSame('in_transit',$shipment->events()->first()->status);
    }

    public function test_message_filters_preserve_source_type_and_intersect_instead_of_widening_results(): void
    {
        $this->actingAs(User::factory()->create());
        $trip=$this->trip(); $shipment=$this->shipment($trip); $other=$this->shipment();
        $contact=TradingContact::firstOrFail();
        $import=TradeImport::create(['kind'=>'payments','file_hash'=>str_repeat('a',64),'currency'=>'USD','created_by'=>auth()->id(),'confirmed_at'=>now()]);
        $voucher=TradeVoucher::create(['source'=>'payments','reference'=>'PAY-FLOW','occurred_at'=>'2026-10-27','kind'=>'supplier_payment','contact_id'=>$contact->id,'amount'=>75,'currency'=>'USD','fingerprint'=>str_repeat('b',64),'import_id'=>$import->id]);
        $wanted=WhatsappMessage::create(['kind'=>'shipment','source_type'=>'shipment','source_id'=>$shipment->id,'contact_id'=>$contact->id,'body'=>'Only this trip notification','status'=>'draft']);
        WhatsappMessage::create(['kind'=>'receipt','source_type'=>'voucher','source_id'=>$voucher->id,'contact_id'=>$contact->id,'body'=>'A voucher is not a trip shipment','status'=>'draft']);
        WhatsappMessage::create(['kind'=>'shipment','source_type'=>'shipment','source_id'=>$other->id,'body'=>'Other shipment notification','status'=>'failed']);
        foreach(['ar','en'] as $locale){
            $this->withSession(['admin_locale'=>$locale])->get(route('dashboard.trade.messages',['trip_id'=>$trip->id]))
                ->assertOk()->assertSee($wanted->body)->assertDontSee('A voucher is not a trip shipment')->assertDontSee('Other shipment notification')
                ->assertSee(route('dashboard.landing.shipments.edit',$shipment),false)->assertSee('name="trip_id"',false);
        }
        $this->get(route('dashboard.trade.messages',['import_id'=>$import->id]))->assertOk()->assertSee('A voucher is not a trip shipment')->assertDontSee($wanted->body);
        $this->get(route('dashboard.trade.messages',['trip_id'=>$trip->id,'status'=>'failed']))->assertOk()->assertDontSee($wanted->body)->assertDontSee('Other shipment notification');
        $this->getJson(route('dashboard.trade.messages',['status'=>'invalid']))->assertUnprocessable();
        $this->getJson(route('dashboard.trade.messages',['trip_id'=>999999]))->assertUnprocessable();
    }

    public function test_confirmed_import_keeps_operation_rows_visible_after_preview_payload_is_cleared(): void
    {
        $this->actingAs(User::factory()->create());
        $contact=TradingContact::create(['number'=>'CONFIRMED','name'=>'Confirmed merchant']);
        $import=TradeImport::create(['kind'=>'payments','file_hash'=>str_repeat('c',64),'currency'=>'USD','created_by'=>auth()->id(),'payload'=>null,'confirmed_at'=>now(),'created_count'=>1]);
        TradeVoucher::create(['source'=>'payments','reference'=>'SAVED-OPERATION','occurred_at'=>'2026-10-27','kind'=>'supplier_payment','contact_id'=>$contact->id,'amount'=>'75.00','currency'=>'USD','fingerprint'=>str_repeat('d',64),'import_id'=>$import->id]);
        $this->get(route('dashboard.trade.preview',$import))->assertOk()->assertSee('SAVED-OPERATION')->assertSee($contact->name)
            ->assertSee(route('dashboard.trade.messages',['import_id'=>$import->id]),false);
        $this->assertNull($import->fresh()->payload);
        $this->assertDatabaseCount('trade_vouchers',1);
    }

    public function test_shipment_import_and_financial_filters_match_only_the_requested_rows(): void
    {
        $this->actingAs(User::factory()->create()); $trip=$this->trip(); $shipment=$this->shipment($trip); $other=$this->shipment();
        $import=ShipmentImport::create(['file_name'=>'report.xlsx','file_hash'=>str_repeat('e',64),'created_by'=>auth()->id()]);
        $shipment->update(['import_id'=>$import->id,'paid_amount'=>null]);
        $params=['import_id'=>$import->id,'attention'=>'financial','draw'=>1,'start'=>0,'length'=>25];
        $this->getJson(route('dashboard.landing.shipments.data',$params))->assertOk()->assertJsonPath('recordsFiltered',1)->assertJsonPath('data.0.id',$shipment->id);
        $this->getJson(route('dashboard.landing.shipments.data',$params+['trip_id'=>$this->trip('UNRELATED')->id]))->assertOk()->assertJsonPath('recordsFiltered',0);
        $this->get(route('dashboard.index'))->assertOk()->assertViewHas('attention',fn($a)=>$a['financial']===1)
            ->assertSee(route('dashboard.landing.shipments',['attention'=>'financial']),false);
        $this->get(route('dashboard.trade.index'))->assertOk()->assertSee('report.xlsx')
            ->assertSee(route('dashboard.landing.shipments',['import_id'=>$import->id]),false);
        $this->trip('FINISHED','delivered');
        $this->getJson(route('dashboard.trips.data',['status'=>'active','draw'=>1,'start'=>0,'length'=>25]))
            ->assertOk()->assertJsonPath('recordsFiltered',2);
    }

    public function test_legacy_event_entry_cannot_skip_stages_and_current_history_cannot_be_deleted(): void
    {
        $this->actingAs(User::factory()->create()); $shipment=$this->shipment();
        $older=$shipment->events()->create(['status'=>'received','happened_at'=>now()->subDay()]);
        $url=route('dashboard.landing.shipments.events.store',$shipment);
        $this->post($url,['status'=>'delivered','happened_at'=>'2026-10-27 12:00'])->assertSessionHasErrors('status');
        $this->post($url,['status'=>'customs','happened_at'=>'2026-10-27 12:00'])->assertSessionHasNoErrors();
        $event=$shipment->events()->reorder()->latest('id')->firstOrFail();
        $this->delete(route('dashboard.landing.shipments.events.delete',[$shipment->id,$event->id]))->assertSessionHasErrors('status');
        $this->assertSame('customs',$shipment->fresh()->status);
        $this->delete(route('dashboard.landing.shipments.events.delete',[$shipment->id,$older->id]))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('landing_shipment_events',1);
    }

    public function test_old_content_delete_links_require_a_mutating_request(): void
    {
        $this->actingAs(User::factory()->create());
        $stat=\App\Models\LandingStat::create(['value'=>'5','label'=>['ar'=>'اختبار','en'=>'Test'],'sort_order'=>0]);
        $url=route('dashboard.landing.stats.delete',$stat);
        $this->get($url)->assertStatus(405);
        $this->assertDatabaseHas('landing_stats',['id'=>$stat->id]);
        $this->delete($url)->assertRedirect();
        $this->assertDatabaseMissing('landing_stats',['id'=>$stat->id]);
    }

    public function test_a_standalone_shipment_notification_can_be_prepared_once_without_an_import(): void
    {
        config(['whatsapp.token'=>null]);
        $shipment=$this->shipment();
        $url=route('dashboard.trade.shipment.prepare',$shipment);
        $this->post($url)->assertRedirect('/login');
        $this->actingAs(User::factory()->create());
        for($i=0;$i<2;$i++)$this->post($url)->assertRedirect(route('dashboard.trade.messages',['shipment_id'=>$shipment->id]));
        $this->assertDatabaseCount('whatsapp_messages',1);
        $this->assertSame('draft',WhatsappMessage::firstOrFail()->status);
        $shipment->update(['merchant_number'=>null]);
        $this->post($url)->assertSessionHasErrors('merchant_number');
        $this->assertDatabaseCount('whatsapp_messages',1);
    }
}
