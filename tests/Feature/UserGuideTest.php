<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ShipmentSpreadsheetImporter;
use App\Support\ShipmentStages;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class UserGuideTest extends TestCase
{
    use RefreshDatabase;

    public function test_guide_requires_dashboard_login(): void
    {
        $this->get('/admin/help')->assertRedirect('/login');
    }

    public function test_both_languages_render_the_complete_guide_with_working_destinations(): void
    {
        $this->actingAs(User::factory()->create());
        $chapterIds = [];
        foreach (['ar', 'en'] as $locale) {
            $response = $this->withSession(['admin_locale' => $locale])->get('/admin/help')->assertOk();
            $response->assertSee(__('guide.title'))->assertSee(config('guide.updated_at'));
            $sections = __('guide.sections');
            $chapterIds[$locale] = array_keys($sections);
            foreach ($sections as $id => $section) {
                if (! in_array($id, config('guide.chapters'), true)) {
                    $response->assertDontSee('id="guide-'.$id.'"', false);
                    continue;
                }
                $response->assertSee('id="guide-'.$id.'"', false)->assertSee($section['title']);
                $this->assertTrue(Route::has($section['route']));
                $this->assertNotEmpty($section['steps']);
                $this->assertNotEmpty($section['faq']);
            }
            foreach (ShipmentStages::labels() as $label) {
                $response->assertSee($label);
            }
            foreach (array_keys(ShipmentSpreadsheetImporter::HEADERS) as $heading) {
                $response->assertSee($heading);
            }
            $response->assertDontSee('WHATSAPP_ACCESS_TOKEN')->assertDontSee('queue:work');
        }
        $this->assertSame($chapterIds['ar'], $chapterIds['en']);
    }

    public function test_dashboard_and_sidebar_link_to_guide(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get('/admin')->assertOk()->assertSee(route('dashboard.help'), false);
        $this->get('/admin/trips')->assertOk()->assertSee(route('dashboard.help'), false);
    }
}
