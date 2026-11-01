<div class="modal fade operations-modal" id="trip-create-modal" tabindex="-1" aria-labelledby="trip-create-title" aria-hidden="true" data-reopen="{{ $errors->tripCreate->any() ? '1' : '0' }}">
    <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
        <div class="modal-header"><div class="operations-modal-heading"><span class="operations-icon"><i class="oi oi-map" aria-hidden="true"></i></span><div><h2 class="modal-title h5" id="trip-create-title">{{ __('Add Trip') }}</h2><p>{{ __('Set the route and dates for your new trip.') }}</p></div></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button></div>
        <form method="POST" action="{{ route('dashboard.trips.store') }}">
            @csrf
            <div class="modal-body">
                @if($errors->tripCreate->any())<div class="alert alert-danger">{{ $errors->tripCreate->first() }}</div>@endif
                <div class="row">
                    @foreach(['reference' => __('Trip Reference'), 'origin' => __('Origin'), 'destination' => __('Destination')] as $field => $label)
                    <div class="col-md-4 mb-3"><label for="new_trip_{{ $field }}">{{ $label }}</label><input id="new_trip_{{ $field }}" class="form-control" name="{{ $field }}" value="{{ old($field) }}" maxlength="{{ $field === 'reference' ? 100 : 191 }}" required></div>
                    @endforeach
                    <div class="col-md-6 mb-3"><label for="new_trip_departure">{{ __('Departure Date') }}</label><input id="new_trip_departure" class="form-control" type="date" name="departure_date" value="{{ old('departure_date') }}"></div>
                    <div class="col-md-6 mb-3"><label for="new_trip_delivery">{{ __('Estimated Delivery') }}</label><input id="new_trip_delivery" class="form-control" type="date" name="estimated_delivery" value="{{ old('estimated_delivery') }}"></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button><button class="btn btn-primary">{{ __('Add Trip') }}</button></div>
        </form>
    </div></div>
</div>
