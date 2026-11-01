<?php

namespace Tests\Feature;

use App\Models\AccountBatch;
use App\Models\LandingLead;
use App\Models\LandingShipment;
use App\Models\LandingTrip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaginationLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_trip_shipments_use_compact_pagination_and_second_page_is_accessible(): void
    {
        $this->actingAs(User::factory()->create());
        $trip = LandingTrip::create(['reference' => '180', 'origin' => 'Cairo', 'destination' => 'Baghdad', 'status' => 'received']);
        for ($i = 1; $i <= 21; $i++) {
            LandingShipment::create(['tracking_code' => 'PAGE-TEST-' . $i, 'trip_id' => $trip->id, 'status' => 'received']);
        }
        $this->get('/admin/trips/' . $trip->id)->assertOk()->assertSee('operations-pagination', false)
            ->assertSee('class="page-link"', false)->assertDontSee('<svg', false)
            ->assertViewHas('shipments', fn ($shipments) => $shipments->count() === 20 && $shipments->total() === 21);
        $this->get('/admin/trips/' . $trip->id . '?page=2')->assertOk()->assertSee('PAGE-TEST-1')
            ->assertViewHas('shipments', fn ($shipments) => $shipments->count() === 1 && $shipments->currentPage() === 2);
    }

    public function test_account_and_lead_pages_share_bootstrap_pagination(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        for ($i = 0; $i < 21; $i++) {
            AccountBatch::create(['title' => 'Statement ' . $i, 'file_hash' => hash('sha256', (string) $i), 'created_by' => $user->id]);
            LandingLead::create(['problem_type' => 'shipping', 'postal_code' => 'Cairo', 'name' => 'Customer', 'phone' => '01000000000']);
        }
        foreach (['/admin/accounts/history', '/admin/landing/leads'] as $url) {
            $this->get($url)->assertOk()->assertSee('class="page-link"', false)->assertDontSee('<svg', false);
        }
    }
}
