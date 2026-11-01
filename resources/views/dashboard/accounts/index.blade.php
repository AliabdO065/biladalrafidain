@extends('dashboard.layouts.layout')
@section('content')
<div class="wrapper"><div class="page"><div class="page-inner ops-list-page">
    <header class="operations-page-header"><div><span class="ops-page-eyebrow">{{ __('Operations Management') }}</span><h1 class="h3">{{ __('Merchant Accounts') }}</h1><p class="text-muted mb-0">{{ __('Fees, collections and remaining balances, directly from shipments.') }}</p></div><a class="btn btn-outline-primary" href="{{ route('dashboard.accounts.history') }}"><i class="oi oi-clock" aria-hidden="true"></i>{{ __('Statement history') }}</a></header>
    @include('dashboard.partials.feedback')
    <p class="alert alert-info">{{ __('trade.opening_hint') }} <a href="{{ route('dashboard.trade.index') }}">{{ __('trade.title') }}</a></p>
    @if(!empty($filters['trip_id']))<a class="btn btn-primary mb-4" href="{{ route('dashboard.accounts.trip', $filters['trip_id']) }}">{{ __('Prepare accounts for all merchants in this trip') }}</a>@endif
    <div id="accounts-table-error" class="alert alert-danger" hidden>{{ __('Could not load the table. Refresh the page and try again.') }}</div>
    <div class="card operations-table-card ops-grid"><div class="card-body operations-table-wrap ops-grid-body">
        <div class="ops-grid-top"><div class="ops-grid-heading"><span class="ops-grid-icon"><i class="oi oi-calculator" aria-hidden="true"></i></span><h2>{{ __('Merchant Register') }}</h2><span class="ops-grid-total" data-table-total="accounts-table">{{ $merchants->total() }}</span></div>
            @include('dashboard.partials.table-search', ['tableId' => 'accounts-table', 'placeholder' => __('Search merchant name or phone'), 'searchValue' => $filters['search'] ?? '', 'searchForm' => 'accounts-filter-form'])
        </div>
        <form id="accounts-filter-form" class="ops-grid-filters" method="GET" action="{{ route('dashboard.accounts.index') }}">
            <label class="ops-grid-select"><i class="oi oi-filter" aria-hidden="true"></i><span class="sr-only">{{ __('Trip') }}</span><select name="trip_id"><option value="">{{ __('All trips') }}</option>@foreach($trips as $trip)<option value="{{ $trip->id }}" @selected((string) ($filters['trip_id'] ?? '') === (string) $trip->id)>{{ __('Trip') }} {{ $trip->reference }}</option>@endforeach</select></label>
            <label class="ops-date-field"><span>{{ __('From date') }}</span><input name="from" type="date" value="{{ $filters['from'] ?? '' }}"></label><span class="ops-filter-divider" aria-hidden="true">—</span><label class="ops-date-field"><span>{{ __('To date') }}</span><input name="to" type="date" value="{{ $filters['to'] ?? '' }}"></label>
            <button class="ops-filter-apply">{{ __('Apply filters') }}</button><a class="ops-filter-clear" href="{{ route('dashboard.accounts.index') }}">{{ __('Reset filters') }}</a>
        </form>
        @php($columns = ['receiver_name', 'receiver_phone', 'currency', 'shipment_count', 'shipping_fee', 'paid_amount', 'balance', 'actions'])
        <table id="accounts-table" class="table" style="width:100%" data-operations-table data-url="{{ route('dashboard.accounts.data') }}" data-field-keys="{{ json_encode($columns) }}" data-extra-filters="{{ json_encode(\Illuminate\Support\Arr::only($filters, ['trip_id','from','to'])) }}" data-initial-search="{{ $filters['search'] ?? '' }}" data-default-order="0" data-default-direction="asc" data-empty-message="{{ __('No shipments match these filters. Add shipments to see merchant accounts here.') }}" data-error="accounts-table-error">
            <thead><tr><th>{{ __('Merchant') }}</th><th>{{ __('Phone') }}</th><th>{{ __('Currency') }}</th><th>{{ __('Shipments') }}</th><th>{{ __('Shipping Fee') }}</th><th>{{ __('Amount Collected') }}</th><th>{{ __('Remaining Balance') }}</th><th>{{ __('Actions') }}</th></tr></thead><tbody>
                @foreach($merchants as $merchant)<tr><td>{{ $merchant->receiver_name ?: __('Unnamed merchant') }}</td><td dir="ltr">{{ $merchant->receiver_phone ?: '—' }}</td><td>{{ $merchant->currency ?: __('Unspecified currency') }}</td><td>{{ $merchant->shipment_count }}</td><td>{{ bcadd((string) ($merchant->shipping_fee ?? 0), '0', 2) }}</td><td>{{ bcadd((string) ($merchant->paid_amount ?? 0), '0', 2) }}</td><td>{{ bcadd((string) ($merchant->balance ?? 0), '0', 2) }}</td><td>@include('dashboard.accounts._actions')</td></tr>@endforeach
            </tbody>
        </table>
        <div class="operations-pagination ops-grid-fallback-pagination">{{ $merchants->onEachSide(1)->links() }}</div>
        <div class="ops-grid-note"><i class="oi oi-info" aria-hidden="true"></i>{{ __('Balances are grouped by currency. Review the statement before sending.') }}</div>
    </div></div>
    @include('dashboard.partials.operations-assets')
</div></div></div>
@endsection
