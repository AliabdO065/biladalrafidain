@extends('dashboard.layouts.layout')
@section('content')
<div class="wrapper"><div class="page"><div class="page-inner ops-list-page">
    <header class="operations-page-header"><div><span class="ops-page-eyebrow">{{ __('Operations Management') }}</span><h1 class="h3">{{ __('Trips') }}</h1><p class="text-muted mb-0">{{ __('Manage every trip, from departure to delivery.') }}</p></div>
        <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#trip-create-modal"><i class="oi oi-plus" aria-hidden="true"></i> {{ __('Add Trip') }}</button>
    </header>
    @include('dashboard.partials.feedback')
    <div id="trips-table-error" class="alert alert-danger" hidden>{{ __('Could not load the table. Refresh the page and try again.') }}</div>
    @php($columns = ['id', 'reference', 'origin', 'destination', 'status', 'departure_date', 'estimated_delivery', 'shipments_count', 'packages_total', 'weight_total', 'created_at', 'actions'])
    @php($basicColumns = ['reference', 'origin', 'destination', 'status', 'shipments_count', 'packages_total', 'weight_total', 'actions'])
    <div class="card operations-table-card ops-grid ops-grid--trips"><div class="card-body operations-table-wrap ops-grid-body">
        <div class="ops-grid-top">
            <div class="ops-grid-heading"><span class="ops-grid-icon"><i class="oi oi-map" aria-hidden="true"></i></span><h2>{{ __('Trip Register') }}</h2><span class="ops-grid-total" data-table-total="trips-table">…</span></div>
            <div class="ops-grid-tools">
                @include('dashboard.partials.table-search', ['tableId' => 'trips-table', 'placeholder' => __('Search by trip number or route…')])
                <label class="ops-grid-select"><i class="oi oi-filter" aria-hidden="true"></i><span class="sr-only">{{ __('Filter by stage') }}</span><select id="trip-status-filter"><option value="">{{ __('All stages') }}</option><option value="active" @selected(request('status') === 'active')>{{ __('Active Trips') }}</option>@foreach(\App\Support\ShipmentStages::labels() as $key => $label)<option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>@endforeach</select></label>
                <button type="button" class="ops-columns-button" data-table-details="trips-table" aria-pressed="false" aria-label="{{ __('Show all details') }}" data-show-label="{{ __('All columns') }}" data-hide-label="{{ __('Basic columns') }}"><i class="oi oi-list-rich" aria-hidden="true"></i><span>{{ __('All columns') }}</span></button>
            </div>
        </div>
        <table id="trips-table" class="table" style="width:100%" data-operations-table data-url="{{ route('dashboard.trips.data') }}" data-field-keys="{{ json_encode($columns) }}" data-basic-columns="{{ json_encode($basicColumns) }}" data-filter="trip-status-filter" data-filter-param="status" data-empty-message="{{ __('No trips yet. Start by adding your first trip.') }}" data-error="trips-table-error">
            <thead><tr>@foreach(['#', __('Trip Reference'), __('From'), __('To'), __('Status'), __('Departure Date'), __('Estimated Delivery'), __('Shipments'), __('Packages'), __('Weight / kg'), __('Created at'), __('Actions')] as $heading)<th>{{ $heading }}</th>@endforeach</tr></thead><tbody></tbody>
        </table>
    </div></div>
    @include('dashboard.trips._create-modal')
    @include('dashboard.partials.operations-assets')
</div></div></div>
@endsection
