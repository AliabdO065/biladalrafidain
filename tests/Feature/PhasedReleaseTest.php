<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\LandingTrip;
use App\Models\LandingShipment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhasedReleaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_deferred_features_are_unavailable_even_with_direct_urls(): void
    {
        $this->actingAs(User::factory()->create());
        foreach (['/catalog', '/catalog/factories', '/factory/login', '/factory/register', '/factory', '/admin/accounts', '/admin/imports', '/admin/merchants', '/admin/whatsapp/messages', '/admin/whatsapp/templates', '/admin/catalog', '/whatsapp/webhook'] as $url) {
            $this->get($url)->assertNotFound();
        }
        foreach (['/factory/register', '/factory/login', '/admin/imports/preview', '/admin/landing/shipments/import', '/whatsapp/webhook'] as $url) {
            $this->post($url)->assertNotFound();
        }
        $this->assertTrue(config('whatsapp.paused'));
    }

    public function test_active_public_and_admin_pages_have_no_deferred_links_in_both_languages(): void
    {
        $user = User::factory()->create();
        foreach (['ar'=>'العربية', 'en'=>'English'] as $code=>$name) {
            \App\Models\Language::updateOrCreate(['code'=>$code], ['name'=>$name, 'native_name'=>$name, 'is_enabled'=>true, 'is_default'=>$code === 'ar', 'sort_order'=>0]);
        }
        $trip = LandingTrip::create(['reference'=>'PHASE-TEST', 'origin'=>'Egypt', 'destination'=>'Iraq', 'status'=>'received']);
        $shipment = LandingShipment::create(['tracking_code'=>'PHASE-SHIPMENT', 'trip_id'=>$trip->id, 'status'=>'received', 'receiver_name'=>'Phase recipient']);
        foreach (['ar', 'en'] as $locale) {
            $this->withSession(['site_locale'=>$locale, 'admin_locale'=>$locale]);
            foreach (['/', '/tracking', '/tracking?code=PHASE-SHIPMENT', '/policies'] as $url) {
                $page = $this->get($url)->assertOk();
                $page->assertSee('lang="'.$locale.'"', false)->assertDontSee('--}}', false)->assertDontSee('{{--', false);
                foreach (['/catalog', '/factory/login', '/factory/register'] as $path) {
                    $page->assertDontSee('href="'.url($path), false);
                }
            }
            $this->actingAs($user);
            foreach (['/admin', '/admin/help', '/admin/trips', '/admin/trips/'.$trip->id, '/admin/landing/shipments', '/admin/landing/shipments/edit/'.$shipment->id] as $url) {
                $page = $this->get($url)->assertOk();
                foreach (['/admin/accounts', '/admin/imports', '/admin/whatsapp', '/admin/catalog', '/admin/merchants'] as $path) {
                    $page->assertDontSee('href="'.url($path), false)->assertDontSee('action="'.url($path), false);
                }
            }
            foreach (['/admin/trips/data', '/admin/landing/shipments/data'] as $url) {
                $this->getJson($url.'?draw=1&start=0&length=25')->assertOk()->assertJsonPath('recordsTotal', 1);
            }
        }
    }
}
