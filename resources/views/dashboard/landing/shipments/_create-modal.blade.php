<div class="modal fade operations-modal" id="shipment-create-modal" tabindex="-1" aria-labelledby="shipment-create-title" aria-hidden="true" data-reopen="{{ $errors->shipmentCreate->any() || request('create') === '1' ? '1' : '0' }}">
    <div class="modal-dialog modal-xl modal-dialog-centered"><div class="modal-content">
        <div class="modal-header"><div class="operations-modal-heading"><span class="operations-icon"><i class="oi oi-box" aria-hidden="true"></i></span><div><h2 class="modal-title h5" id="shipment-create-title">{{ __('Add Shipment') }}</h2><p>{{ __('Enter the shipment details and choose its trip.') }}</p></div></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button></div>
        <form action="{{ route('dashboard.landing.shipments.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                @if($errors->shipmentCreate->any())<div class="alert alert-danger">{{ $errors->shipmentCreate->first() }}</div>@endif
                @include('dashboard.landing.shipments._fields')
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button><button class="btn btn-primary">{{ __('Save') }}</button></div>
        </form>
    </div></div>
</div>
