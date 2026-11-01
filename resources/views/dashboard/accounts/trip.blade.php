@extends('dashboard.layouts.layout')
@section('content')
<div class="wrapper"><div class="page"><div class="page-inner operations-detail-page">
    <a href="{{ route('dashboard.trips.show', $trip) }}">{{ __('Trip') }} {{ $trip->reference }}</a>
    <header class="operations-page-header mt-3"><div><h1 class="h3">{{ __('Trip merchant accounts') }} — {{ $trip->reference }}</h1><p class="text-muted mb-0">{{ __('All merchants in this trip, with a separate account message for each merchant and currency.') }}</p></div></header>
    @include('dashboard.partials.feedback')
    @if(!$configured)<div class="alert alert-info">{{ __('manual.mode') }}</div>@endif
    @php($merchantCount = collect($groups)->pluck('key')->unique()->count())
    @php($incomplete = collect($groups)->sum('incomplete'))
    <div class="alert trip-account-summary">{{ __(':merchants merchants, :shipments shipments. Only shipments belonging to this trip are included.', ['merchants' => $merchantCount, 'shipments' => collect($groups)->sum('count')]) }}</div>
    @if($incomplete)<div class="alert alert-warning">{{ __(':count shipments have incomplete financial data. Complete their shipping fees and collected amounts before sending.', ['count' => $incomplete]) }}</div>@endif
    @if($groups)
    <form id="trip-account-form" method="POST" action="{{ route('dashboard.accounts.trip.prepare', $trip) }}" data-once>
        @csrf<input type="hidden" name="version" value="{{ $version }}">
        <div class="card mb-4"><div class="card-body"><h2 class="h5">{{ __('Shared defaults for this batch') }}</h2><p class="text-muted">{{ __('These choices apply only where a row has no individual choice. Known shipment currencies remain unchanged.') }}</p>
            <div class="row">
                <div class="col-md-6 mb-3"><label for="trip-default-country">{{ __('Country for local numbers') }}</label><select class="form-control" id="trip-default-country" name="default_country_code"><option value="">{{ __('Choose a country if numbers are local') }}</option><option value="964" @selected(old('default_country_code') === '964')>{{ __('Iraq') }} +964</option><option value="20" @selected(old('default_country_code') === '20')>{{ __('Egypt') }} +20</option></select></div>
                <div class="col-md-6 mb-3"><label for="trip-default-currency">{{ __('Currency for unspecified shipment amounts') }}</label><select class="form-control" id="trip-default-currency" name="default_currency"><option value="">{{ __('Choose a currency if unspecified') }}</option>@foreach(['EGP','IQD','USD','EUR','SAR','AED','TRY'] as $currency)<option value="{{ $currency }}" @selected(old('default_currency') === $currency)>{{ $currency }}</option>@endforeach</select></div>
            </div>
        </div></div>
        <div class="card"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>{{ __('Merchant') }}</th><th>{{ __('Shipments') }}</th><th>{{ __('Shipping Fee') }}</th><th>{{ __('Amount Collected') }}</th><th>{{ __('Remaining Balance') }}</th><th>{{ __('WhatsApp number') }}</th><th>{{ __('Country for local numbers') }}</th><th>{{ __('Currency') }}</th></tr></thead><tbody>
            @foreach($groups as $slot => $summary)
            <tr data-trip-recipient="{{ $slot }}"><td>{{ $summary['name'] }}<a class="d-block small" href="{{ route('dashboard.accounts.merchant', ['key' => $summary['key'], 'trip_id' => $trip->id, 'bucket' => $summary['bucket'] ?? '']) }}" target="_blank" rel="noopener">{{ __('Review accounts') }}</a>@if($summary['phone_count'] > 1)<small class="d-block text-warning">{{ __('Multiple phone numbers: confirm the recipient.') }}</small>@endif</td>
                <td>{{ $summary['count'] }}</td><td>{{ $summary['fee'] }}</td><td>{{ $summary['paid'] }}</td><td>{{ $summary['balance'] }}@if($summary['incomplete'])<small class="d-block text-warning">{{ __('Incomplete amounts') }}</small>@endif</td>
                <td><input class="form-control trip-recipient-phone" type="tel" dir="ltr" name="recipients[{{ $slot }}][phone]" data-recipient-field="phone" value="{{ old('recipients.'.$slot.'.phone', $summary['phone']) }}" maxlength="50" required aria-label="{{ __('WhatsApp number') }} — {{ $summary['name'] }}"></td>
                <td><select class="form-control trip-recipient-select" name="recipients[{{ $slot }}][country_code]" data-recipient-field="country_code" aria-label="{{ __('Country for local numbers') }} — {{ $summary['name'] }}"><option value="">{{ __('Use shared choice') }}</option><option value="964" @selected(old('recipients.'.$slot.'.country_code') === '964')>{{ __('Iraq') }} +964</option><option value="20" @selected(old('recipients.'.$slot.'.country_code') === '20')>{{ __('Egypt') }} +20</option></select></td>
                <td>@if($summary['currency'])<input class="form-control trip-recipient-select" name="recipients[{{ $slot }}][currency]" data-recipient-field="currency" value="{{ $summary['currency'] }}" readonly aria-label="{{ __('Currency') }} — {{ $summary['name'] }}">
                    @else<select class="form-control trip-recipient-select" name="recipients[{{ $slot }}][currency]" data-recipient-field="currency" aria-label="{{ __('Currency') }} — {{ $summary['name'] }}"><option value="">{{ __('Use shared choice') }}</option>@foreach(['EGP','IQD','USD','EUR','SAR','AED','TRY'] as $currency)<option value="{{ $currency }}" @selected(old('recipients.'.$slot.'.currency') === $currency)>{{ $currency }}</option>@endforeach</select>@endif</td>
            </tr>@endforeach
        </tbody></table></div></div>
        <div class="card mt-4"><div class="card-body"><label class="d-block mb-3"><input type="checkbox" name="reviewed" value="1" required> {{ __('I reviewed all merchants, phone numbers, currencies and amounts in this trip.') }}</label><button class="btn btn-primary" @disabled($incomplete > 0)>{{ __('Prepare all trip account messages') }}</button><p class="text-muted mt-3 mb-0">{{ __('manual.instructions') }}</p></div></div>
    </form>
    @else<div class="card"><div class="card-body text-muted">{{ __('This trip has no shipments yet.') }}</div></div>@endif
</div></div></div>
@endsection
@push('scripts')
<script src="{{ asset('dashboard/assets/javascript/trip-accounts.js') }}?v={{ filemtime(public_path('dashboard/assets/javascript/trip-accounts.js')) }}"></script>
@endpush
