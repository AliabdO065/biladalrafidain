<div class="ops-row-actions">
    <a class="ops-action-main" href="{{ route('dashboard.trips.show', $trip) }}"><i class="oi oi-eye" aria-hidden="true"></i>{{ __('View trip') }}</a>
    <a class="ops-action-icon" href="{{ route('dashboard.landing.shipments', ['trip_id' => $trip->id]) }}" title="{{ __('Shipments') }}" aria-label="{{ __('Shipments') }}"><i class="oi oi-box" aria-hidden="true"></i></a>
    {{-- Phase 3/4: <a class="ops-action-icon" href="{{ route('dashboard.accounts.trip', $trip) }}" title="{{ __('Merchant Accounts') }}" aria-label="{{ __('Merchant Accounts') }}"><i class="oi oi-calculator" aria-hidden="true"></i></a> --}}
    @include('dashboard.trips._delete')
</div>
