<?php
namespace Tests\Feature;

use App\Models\LandingShipment;
use App\Services\ShipmentDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Cache,File,Http};
use Tests\TestCase;

class SharedHostingTest extends TestCase
{
    use RefreshDatabase;

    public function test_bill_images_render_in_both_languages_without_chrome_and_restore_locale(): void
    {
        config(['documents.renderer'=>'gd','whatsapp.chrome_binary'=>'/no-browser-installed']);
        $shipment=new LandingShipment(['tracking_code'=>'BR-188-2026','receiver_name'=>'ريان عبدالوهاب','merchant_number'=>'BR-119','sender_name'=>'مصنع عبدالرحمن','city'=>'بغداد','packages_count'=>2,'weight_kg'=>'77.000','shipping_fee'=>'61.60','paid_amount'=>'0.00','balance'=>'61.60','currency'=>'USD','status'=>'in_transit','notes'=>str_repeat('ملاحظة ',100)]);
        $shipment->created_at=now();$shipment->setRelation('trip',null);
        $original=app()->getLocale();$hashes=[];
        foreach(['ar','en']as$locale){
            $document=app(ShipmentDocument::class)->render($shipment,$locale);
            try{
                $info=getimagesize($document['path']);
                $this->assertSame([1130,1600],[$info[0],$info[1]]);
                $this->assertSame('image/png',$info['mime']);
                $this->assertLessThan(5*1024*1024,filesize($document['path']));
                $this->assertFileDoesNotExist($document['folder'].'/document.html');
                $this->assertSame($original,app()->getLocale());
                $hashes[]=hash_file('sha256',$document['path']);
            }finally{File::deleteDirectory($document['folder']);}
        }
        $this->assertNotSame($hashes[0],$hashes[1]);
    }

    public function test_shared_tick_leaves_messages_untouched_when_disconnected_and_prevents_overlap(): void
    {
        config(['whatsapp.token'=>null]);$this->travelTo(now()->setTime(13,2));
        Cache::forget('hosting:last_tick');
        $lock=Cache::lock('hosting:tick-lock',240);$this->assertTrue($lock->get());
        try{$this->artisan('hosting:tick')->expectsOutput('Another hosting tick is running.')->assertExitCode(0);}
        finally{$lock->release();}
        $this->assertNull(Cache::get('hosting:last_tick'));
        $this->artisan('hosting:tick')->assertExitCode(0);
        $this->assertNotNull(Cache::get('hosting:last_tick'));
    }

    public function test_configured_tick_finishes_when_the_notification_queue_is_empty(): void
    {
        config(['whatsapp.token'=>'test-token','whatsapp.phone_number_id'=>'123456','whatsapp.version'=>'v23.0','whatsapp.paused'=>false,'hosting.worker_seconds'=>10]);
        Http::preventStrayRequests();$this->travelTo(now()->setTime(13,2));
        $this->artisan('hosting:tick')->assertExitCode(0);
        Http::assertNothingSent();
        $this->assertNotNull(Cache::get('hosting:last_tick'));
    }

    public function test_readiness_rejects_development_settings_for_a_production_launch(): void
    {
        config(['app.debug'=>true,'session.secure'=>false,'app.url'=>'http://localhost']);
        $this->artisan('hosting:check',['--production'=>true])->assertExitCode(1);
        $this->artisan('hosting:check')->assertExitCode(0);
    }

    public function test_private_pages_are_not_cached_and_responses_disallow_mime_sniffing(): void
    {
        $this->actingAs(\App\Models\User::factory()->create());
        $this->get('/admin')->assertOk()->assertHeader('X-Content-Type-Options','nosniff')
            ->assertHeader('X-Frame-Options','SAMEORIGIN')->assertHeader('X-Robots-Tag','noindex, nofollow')
            ->assertHeaderContains('Cache-Control','no-store');
    }

    public function test_admin_logout_requires_post_and_a_get_link_cannot_end_the_session(): void
    {
        $this->actingAs(\App\Models\User::factory()->create());
        $this->get('/logout')->assertStatus(405);
        $this->assertAuthenticated('web');
        $this->post('/logout')->assertRedirect('/home');
        $this->assertGuest('web');
    }
}
