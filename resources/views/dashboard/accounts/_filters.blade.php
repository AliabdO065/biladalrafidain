<form class="account-filters" method="GET" action="{{ $filterAction ?? route('dashboard.accounts.index') }}">
    @if(isset($key))<input type="hidden" name="bucket" value="{{ $filters['bucket'] ?? '' }}">@endif
    @if(!isset($key))<div><label for="account-search">{{ __('Merchant') }}</label><input id="account-search" class="form-control" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('Search merchant name or phone') }}"></div>@endif
    <div><label for="account-trip">{{ __('Trip') }}</label><select id="account-trip" class="form-control" name="trip_id"><option value="">{{ __('All trips') }}</option>@foreach($trips as $trip)<option value="{{ $trip->id }}" @selected((string) ($filters['trip_id'] ?? '') === (string) $trip->id)>{{ $trip->reference }}</option>@endforeach</select></div>
    <div><label for="account-from">{{ __('From date') }}</label><input id="account-from" class="form-control" type="date" name="from" value="{{ $filters['from'] ?? '' }}"></div>
    <div><label for="account-to">{{ __('To date') }}</label><input id="account-to" class="form-control" type="date" name="to" value="{{ $filters['to'] ?? '' }}"></div>
    <button class="btn btn-primary">{{ __('Apply filters') }}</button>
    <a class="btn btn-light" href="{{ isset($key) ? route('dashboard.accounts.merchant', ['key' => $key, 'bucket' => $filters['bucket'] ?? '']) : route('dashboard.accounts.index') }}">{{ __('Reset filters') }}</a>
</form>
