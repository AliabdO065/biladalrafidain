<?php

namespace App\Http\Controllers;

use App\Models\LandingTrip;
use App\Models\AccountBatch;
use App\Support\ShipmentStages;
use App\Support\DataTableResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class TripController extends Controller
{
    public function index()
    {
        return view('dashboard.trips.index');
    }

    public function data(Request $request)
    {
        $request->validate(['status' => ['nullable', Rule::in(array_merge(array_keys(ShipmentStages::labels()), ['active']))]]);
        $query = LandingTrip::withCount('shipments')->withSum('shipments as packages_total', 'packages_count')->withSum('shipments as weight_total', 'weight_kg');
        if ($request->input('status') === 'active') {
            $query->where('status', '!=', 'delivered');
        } elseif ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return DataTableResponse::make($request, $query,
            ['id', 'reference', 'origin', 'destination', 'status', 'departure_date', 'estimated_delivery', 'shipments_count', 'packages_total', 'weight_total', 'created_at'],
            ['reference', 'origin', 'destination', 'status'], function ($trip) {
                return [
                    'id' => $trip->id, 'reference' => $trip->reference, 'origin' => $trip->origin, 'destination' => $trip->destination,
                    'status' => ShipmentStages::label($trip->status), 'departure_date' => optional($trip->departure_date)->format('Y-m-d'),
                    'status_code' => $trip->status, 'view_url' => route('dashboard.trips.show', $trip),
                    'estimated_delivery' => optional($trip->estimated_delivery)->format('Y-m-d'), 'shipments_count' => $trip->shipments_count,
                    'packages_total' => $trip->packages_total ?? 0, 'weight_total' => $trip->weight_total ?? '0',
                    'created_at' => $trip->created_at->format('Y-m-d H:i'),
                    'actions' => view('dashboard.trips._actions', compact('trip'))->render(),
                ];
            }, [['column' => 'status', 'labels' => ShipmentStages::labels()]]);
    }

    public function store(Request $request)
    {
        $data = $request->validateWithBag('tripCreate', [
            'reference' => 'required|string|max:100|unique:landing_trips,reference',
            'origin' => 'required|string|max:191',
            'destination' => 'required|string|max:191',
            'departure_date' => 'nullable|date',
            'estimated_delivery' => ['nullable', 'date', Rule::when($request->filled('departure_date'), 'after_or_equal:departure_date')],
        ]);
        $trip = DB::transaction(function () use ($data) {
            $trip = LandingTrip::create($data + ['status' => 'received']);
            $trip->events()->create(['status' => 'received', 'happened_at' => now()]);

            return $trip;
        });

        return redirect()->route('dashboard.trips.show', $trip)->with('success', __('Trip created.'));
    }

    public function show(LandingTrip $trip)
    {
        $trip->load('events');
        $shipments = $trip->shipments()->latest('id')->paginate(20);
        $trips = LandingTrip::latest()->get();
        $selectedTripId = $trip->id;

        return view('dashboard.trips.show', compact('trip', 'shipments', 'trips', 'selectedTripId'));
    }

    public function update(Request $request, LandingTrip $trip)
    {
        $data = $request->validate([
            'reference' => 'required|string|max:100|unique:landing_trips,reference,' . $trip->id,
            'origin' => 'required|string|max:191',
            'destination' => 'required|string|max:191',
            'departure_date' => 'nullable|date',
            'estimated_delivery' => ['nullable', 'date', Rule::when($request->filled('departure_date'), 'after_or_equal:departure_date')],
        ]);
        $trip->update($data);

        return back()->with('success', __('Trip details updated.'));
    }

    public function destroy(LandingTrip $trip)
    {
        DB::transaction(function () use ($trip) {
            $locked = LandingTrip::lockForUpdate()->findOrFail($trip->id);
            if ($locked->shipments()->exists()) {
                throw ValidationException::withMessages(['trip' => __('Move or delete this trip shipments before deleting the trip.')]);
            }
            if (AccountBatch::where('source_filters->trip_id', $locked->id)->exists()) {
                throw ValidationException::withMessages(['trip' => __('This trip has saved account statements and cannot be deleted.')]);
            }
            $locked->delete();
        });

        return redirect()->route('dashboard.trips.index')->with('success', __('Trip deleted.'));
    }

    public function advance(Request $request, LandingTrip $trip)
    {
        $request->validate(['expected_status' => 'required|string']);
        DB::transaction(function () use ($trip, $request) {
            $locked = LandingTrip::lockForUpdate()->findOrFail($trip->id);
            if ($locked->status !== $request->input('expected_status')) {
                throw ValidationException::withMessages(['status' => __('The trip has already changed. Refresh and try again.')]);
            }
            $next = ShipmentStages::next($locked->status);
            if (! $next) {
                throw ValidationException::withMessages(['status' => __('This trip is already complete.')]);
            }
            $locked->update(['status' => $next]);
            $locked->events()->create(['status' => $next, 'happened_at' => now()]);
        });

        return back()->with('success', __('Trip advanced to the next stage.'));
    }
}
