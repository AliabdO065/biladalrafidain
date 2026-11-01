<?php

namespace Tests\Feature;

use App\Models\AccountBatch;
use App\Models\LandingShipment;
use App\Models\LandingTrip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_trip_can_be_deleted_with_its_stage_history(): void
    {
        $this->actingAs(User::factory()->create());
        $trip = $this->trip();
        $trip->events()->create(['status' => 'received', 'happened_at' => now()]);
        $this->delete('/admin/trips/' . $trip->id)->assertRedirect(route('dashboard.trips.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('landing_trips', ['id' => $trip->id]);
        $this->assertDatabaseMissing('landing_trip_events', ['trip_id' => $trip->id]);
    }

    public function test_trip_with_shipments_is_preserved(): void
    {
        $this->actingAs(User::factory()->create());
        $trip = $this->trip();
        $shipment = LandingShipment::create(['tracking_code' => 'KEEP-SHIPMENT', 'trip_id' => $trip->id]);
        $this->from('/admin/trips')->delete('/admin/trips/' . $trip->id)->assertSessionHasErrors('trip');
        $this->assertDatabaseHas('landing_trips', ['id' => $trip->id]);
        $this->assertDatabaseHas('landing_shipments', ['id' => $shipment->id, 'trip_id' => $trip->id]);
    }

    public function test_trip_with_saved_account_statement_is_preserved(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $trip = $this->trip();
        AccountBatch::create(['title' => 'Saved account', 'file_hash' => str_repeat('a',64), 'created_by' => $user->id,
            'source' => 'trip_shipments', 'source_filters' => ['trip_id' => $trip->id]]);
        $this->delete('/admin/trips/' . $trip->id)->assertSessionHasErrors('trip');
        $this->assertDatabaseHas('landing_trips', ['id' => $trip->id]);
    }

    public function test_delete_requires_login_and_get_does_not_delete(): void
    {
        $trip = $this->trip();
        $this->delete('/admin/trips/' . $trip->id)->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/admin/trips/' . $trip->id)->assertOk()->assertSee('name="_method" value="DELETE"', false);
        $this->assertDatabaseHas('landing_trips', ['id' => $trip->id]);
    }

    private function trip(): LandingTrip
    {
        return LandingTrip::create(['reference' => 'DELETE-TEST', 'origin' => 'Cairo', 'destination' => 'Baghdad']);
    }
}
