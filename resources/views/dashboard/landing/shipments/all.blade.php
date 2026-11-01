@extends('dashboard.layouts.layout')
@section('content')
<div class="wrapper"><div class="page"><div class="page-inner ops-list-page">
    <header class="operations-page-header"><div><span class="ops-page-eyebrow">{{ __('Operations Management') }}</span><h1 class="h3">{{ __('Shipments') }}</h1><p class="text-muted mb-0">{{ __('Shipment details, recipients and progress, clearly organized.') }}</p></div>
        <div class="operations-row-actions">{{-- <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#shipment-import-modal"><i class="oi oi-data-transfer-upload" aria-hidden="true"></i>{{ __('Import shipment receipts') }}</button> --}}<button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#shipment-create-modal"><i class="oi oi-plus" aria-hidden="true"></i>{{ __('Add Shipment') }}</button></div>
    </header>
    @include('dashboard.partials.feedback')
    @if(!empty($shipmentFilters['import_id']) || !empty($shipmentFilters['attention']))<div class="flow-context"><div><strong>{{ __('flow.filtered') }}</strong><small>{{ !empty($shipmentFilters['import_id']) ? __('flow.import_number').' #'.$shipmentFilters['import_id'] : __('flow.financial_hint') }}</small></div><div class="operations-row-actions">@if(!empty($shipmentFilters['import_id'])){{-- Phase 3/4: <a class="btn btn-outline-primary" href="{{ route('dashboard.trade.messages',['shipment_import_id'=>$shipmentFilters['import_id']]) }}">{{ __('flow.import_messages') }}</a> --}}@endif<a class="btn btn-light" href="{{ route('dashboard.landing.shipments') }}">{{ __('flow.clear') }}</a></div></div>@endif
    <div id="shipments-table-error" class="alert alert-danger" hidden>{{ __('Could not load the table. Refresh the page and try again.') }}</div>
    <div class="card operations-table-card ops-grid"><div class="card-body operations-table-wrap ops-grid-body">
        <div class="ops-grid-top">
            <div class="ops-grid-heading"><span class="ops-grid-icon"><i class="oi oi-box" aria-hidden="true"></i></span><h2>{{ __('Shipment Register') }}</h2><span class="ops-grid-total" data-table-total="shipments-table">…</span></div>
            <div class="ops-grid-tools">
                @include('dashboard.partials.table-search', ['tableId' => 'shipments-table', 'placeholder' => __('Search a shipment or recipient…')])
                <label class="ops-grid-select"><i class="oi oi-filter" aria-hidden="true"></i><span class="sr-only">{{ __('Filter by trip') }}</span><select id="shipment-trip-filter"><option value="">{{ __('All trips') }}</option>@foreach($trips as $trip)<option value="{{ $trip->id }}" @selected((string) request('trip_id') === (string) $trip->id)>{{ __('Trip') }} {{ $trip->reference }}</option>@endforeach</select></label>
                <button type="button" class="ops-columns-button" data-table-details="shipments-table" aria-pressed="false" aria-label="{{ __('Show all details') }}" data-show-label="{{ __('All columns') }}" data-hide-label="{{ __('Basic columns') }}"><i class="oi oi-list-rich" aria-hidden="true"></i><span>{{ __('All columns') }}</span></button>
            </div>
        </div>
        @php($columns = array_merge(['id', 'tracking_code', 'trip_reference'], array_keys(\App\Support\ShipmentFields::labels()), ['origin', 'destination', 'estimated_delivery', 'status', 'created_at', 'actions']))
        @php($basicColumns = ['tracking_code', 'trip_reference', 'receiver_name', 'city', 'packages_count', 'weight_kg', 'status', 'actions'])
        <table id="shipments-table" class="table" style="width:100%" data-operations-table data-url="{{ route('dashboard.landing.shipments.data') }}" data-field-keys="{{ json_encode($columns) }}" data-extra-filters="{{ json_encode(\Illuminate\Support\Arr::except($shipmentFilters,['trip_id'])) }}" data-basic-columns="{{ json_encode($basicColumns) }}" data-filter="shipment-trip-filter" data-error="shipments-table-error">
            <thead><tr><th>#</th><th>{{ __('Tracking Code') }}</th><th>{{ __('Trip Reference') }}</th>@foreach(\App\Support\ShipmentFields::labels() as $label)<th>{{ $label }}</th>@endforeach<th>{{ __('Origin') }}</th><th>{{ __('Destination') }}</th><th>{{ __('Estimated Delivery') }}</th><th>{{ __('Status') }}</th><th>{{ __('Created at') }}</th><th>{{ __('Actions') }}</th></tr></thead><tbody></tbody>
        </table>
    </div></div>
    @include('dashboard.landing.shipments._create-modal')
    {{-- @include('dashboard.landing.shipments._import-modal') --}}
    @include('dashboard.partials.operations-assets')
</div></div></div>
@endsection
