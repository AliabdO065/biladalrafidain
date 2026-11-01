@extends('dashboard.layouts.layout')
@section('content')
<div class="wrapper"><div class="page"><div class="page-inner operations-detail-page">
    <a href="{{ route('dashboard.trips.index') }}">{{ __('Trips') }}</a>
    <h1 class="h3 my-3">{{ __('Trip Reference') }}: {{ $trip->reference }}</h1>
    <div class="operations-row-actions mb-3">{{-- Phase 3/4: <a href="{{ route('dashboard.accounts.trip', $trip) }}" class="btn btn-outline-primary">{{ __('Trip merchant accounts') }}</a> --}}@include('dashboard.trips._delete', ['tripShipmentCount' => $shipments->total(), 'deleteClass' => 'btn btn-outline-danger', 'showDeleteLabel' => true])</div>
    @if($shipments->total())<p class="text-muted small">{{ __('Move or delete this trip shipments before deleting the trip.') }}</p>@endif
    @include('dashboard.partials.feedback')
    <div class="flow-context"><div><strong>{{ __('Trip') }} {{ $trip->reference }}</strong><small>{{ __('release.dashboard_hint') }}</small></div><div class="operations-row-actions"><a class="btn btn-light" href="{{ route('dashboard.landing.shipments',['trip_id'=>$trip->id]) }}">{{ __('Shipments') }}</a>{{-- Phase 3/4: <a class="btn btn-outline-primary" href="{{ route('dashboard.trade.messages',['trip_id'=>$trip->id]) }}">{{ __('flow.shipment_messages') }}</a> --}}</div></div>
    {{-- Phase 3/4: <form method="POST" action="{{ route('dashboard.trade.trip.documents',$trip) }}" class="mb-3" data-once>@csrf<button class="btn btn-outline-primary"><i class="oi oi-document" aria-hidden="true"></i> {{ __('trade.prepare_trip') }}</button><small class="d-block text-muted mt-2">{{ __('trade.intro') }}</small></form> --}}
    <div class="card mb-4"><div class="card-body">
        <h2 class="h5">{{ $trip->origin }} ← {{ $trip->destination }}</h2>
        <p>{{ __('Departure Date') }}: {{ optional($trip->departure_date)->format('Y-m-d') ?? '—' }} · {{ __('Estimated Delivery') }}: {{ optional($trip->estimated_delivery)->format('Y-m-d') ?? '—' }}</p>
        @include('dashboard.partials.stages', ['status' => $trip->status])
        @if($next = \App\Support\ShipmentStages::next($trip->status))
        <form method="POST" action="{{ route('dashboard.trips.advance', $trip) }}" data-once>
            @csrf<input type="hidden" name="expected_status" value="{{ $trip->status }}">
            <button class="btn btn-primary">{{ __('Move to') }}: {{ \App\Support\ShipmentStages::label($next) }}</button>
        </form>
        @else<p class="text-success">{{ __('This trip is already complete.') }}</p>@endif
        <p class="text-muted mt-3 mb-0">{{ __('All shipments on this trip follow its current stage.') }}</p>
    </div></div>
    <div class="card mb-4"><div class="card-body">
        <h2 class="h5">{{ __('Trip details') }}</h2>
        <form method="POST" action="{{ route('dashboard.trips.update', $trip) }}">
            @csrf
            <div class="row">
            @foreach(['reference' => __('Trip Reference'), 'origin' => __('Origin'), 'destination' => __('Destination')] as $field => $label)
                <div class="col-md-4 mb-3"><label for="{{ $field }}">{{ $label }}</label><input id="{{ $field }}" class="form-control" name="{{ $field }}" value="{{ old($field, $trip->$field) }}" maxlength="{{ $field === 'reference' ? 100 : 191 }}" required></div>
            @endforeach
                <div class="col-md-6 mb-3"><label for="departure_date">{{ __('Departure Date') }}</label><input id="departure_date" class="form-control" type="date" name="departure_date" value="{{ old('departure_date', optional($trip->departure_date)->format('Y-m-d')) }}"></div>
                <div class="col-md-6 mb-3"><label for="estimated_delivery">{{ __('Estimated Delivery') }}</label><input id="estimated_delivery" class="form-control" type="date" name="estimated_delivery" value="{{ old('estimated_delivery', optional($trip->estimated_delivery)->format('Y-m-d')) }}"></div>
            </div>
            <button class="btn btn-outline-primary">{{ __('Save') }}</button>
        </form>
    </div></div>
    <div class="card mb-4"><div class="card-body">
        <h2 class="h5">{{ __('Tracking History') }}</h2>
        @foreach($trip->events as $event)<p>{{ \App\Support\ShipmentStages::label($event->status) }} <time class="text-muted">{{ $event->happened_at->format('Y-m-d H:i') }}</time></p>@endforeach
    </div></div>
    <div class="operations-section-header"><h2 class="h5">{{ __('Shipments') }}</h2><div class="operations-row-actions"><a class="btn btn-outline-primary" href="{{ route('dashboard.landing.shipments', ['trip_id' => $trip->id]) }}">{{ __('All shipment details') }}</a><button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#shipment-create-modal">{{ __('Add Shipment') }}</button></div></div>
    <div class="card"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>{{ __('Tracking Code') }}</th><th>{{ __('Receiver') }}</th><th></th></tr></thead><tbody>
        @forelse($shipments as $shipment)<tr><td><a href="{{ route('fronted.tracking', ['code' => $shipment->tracking_code]) }}" target="_blank" rel="noopener">{{ $shipment->tracking_code }}</a></td><td>{{ $shipment->receiver_name }}</td><td><a href="{{ route('dashboard.landing.shipments.edit', $shipment) }}">{{ __('Edit') }}</a></td></tr>
        @empty<tr><td colspan="3">{{ __('No shipments yet.') }}</td></tr>@endforelse
    </tbody></table></div></div>
    <div class="operations-pagination">{{ $shipments->onEachSide(1)->links() }}</div>
    @include('dashboard.landing.shipments._create-modal')
    @include('dashboard.partials.operations-assets')
</div></div></div>
@endsection
