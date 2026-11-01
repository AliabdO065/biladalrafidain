<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\LandingSetting;
use App\Models\User;
use App\Support\ShipmentFields;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LanguagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_website_only_offers_arabic_and_english_even_with_legacy_rows(): void
    {
        Language::create(['code' => 'tr', 'name' => 'Turkish', 'native_name' => 'Türkçe', 'is_enabled' => true, 'is_default' => true]);
        Language::create(['code' => 'he', 'name' => 'Hebrew', 'native_name' => 'עברית', 'is_enabled' => true]);
        $this->withSession(['site_locale' => 'he'])->get('/')->assertOk()
            ->assertViewHas('enabledLanguages', fn ($languages) => $languages->pluck('code')->all() === ['ar', 'en'])
            ->assertSessionHas('site_locale', 'ar')->assertDontSee('Türkçe')->assertDontSee('עברית');
        $this->get('/lang/tr')->assertNotFound();
        $this->get('/lang/he')->assertNotFound();
        $this->from('/')->get('/lang/en')->assertSessionHas('site_locale', 'en');
        $this->get('/')->assertSee('lang="en"', false)->assertSee('dir="ltr"', false);
        $this->assertDatabaseHas('languages', ['code' => 'he']);
    }

    public function test_language_management_and_content_tabs_only_have_two_languages(): void
    {
        $this->actingAs(User::factory()->create());
        Language::create(['code' => 'tr', 'name' => 'Turkish', 'native_name' => 'Türkçe', 'is_enabled' => true]);
        $this->get('/admin/landing/languages')->assertOk()->assertViewHas('languages', fn ($languages) => $languages->pluck('code')->all() === ['ar', 'en'])->assertDontSee('Türkçe');
        $this->get('/admin/landing/settings')->assertOk()->assertSee('English')->assertSee('العربية')->assertDontSee('[tr]', false)->assertDontSee('[he]', false);
        $this->post('/admin/landing/languages/update', ['enabled' => ['ar' => 1, 'en' => 1, 'tr' => 1]])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('languages', ['code' => 'tr', 'is_enabled' => false]);
        $this->assertDatabaseHas('languages', ['code' => 'ar', 'is_enabled' => true]);
    }

    public function test_shipment_fields_and_login_switch_between_arabic_and_english(): void
    {
        $this->get('/login/lang/en')->assertRedirect('/login');
        $this->get('/login')->assertOk()->assertSee('lang="en"', false)->assertSee('Email Address');
        $this->get('/login/lang/ar')->assertRedirect('/login');
        $this->get('/login')->assertOk()->assertSee('dir="rtl"', false)->assertSee('البريد الإلكتروني');
        $this->actingAs(User::factory()->create());
        $this->withSession(['admin_locale' => 'en'])->get('/admin/landing/shipments')->assertOk()->assertSee('Factory / Sender')->assertSee('Shipping Fee')->assertDontSee('المصنع / المرسل');
        $this->withSession(['admin_locale' => 'ar'])->get('/admin/landing/shipments')->assertOk()->assertSee('المصنع / المرسل')->assertSee('أجرة الشحن');
        app()->setLocale('en');
        $this->assertSame('Receipt Number', ShipmentFields::labels()['receipt_number']);
        app()->setLocale('ar');
        $this->assertSame('رقم السند', ShipmentFields::labels()['receipt_number']);
    }

    public function test_legacy_content_is_preserved_but_never_used_as_a_fallback(): void
    {
        $settings = LandingSetting::create([]);
        DB::table('landing_settings')->where('id', $settings->id)->update(['hero_headline' => json_encode(['ar' => '', 'en' => '', 'tr' => 'Legacy Turkish'])]);
        $settings->refresh();
        app()->setLocale('en');
        $this->assertNull($settings->hero_headline);
        $settings->update(['hero_headline' => ['ar' => 'عنوان عربي', 'en' => 'English title', 'he' => 'New unsupported value']]);
        $all = $settings->fresh()->translationsFor('hero_headline');
        $this->assertSame('Legacy Turkish', $all['tr']);
        $this->assertArrayNotHasKey('he', $all);
        $this->assertSame('English title', $settings->fresh()->hero_headline);
    }
}
