<div class="modal fade operations-modal" id="shipment-import-modal" tabindex="-1" aria-labelledby="shipment-import-title" aria-hidden="true" data-reopen="{{ $errors->shipmentImport->any() || request('import') == 1 ? '1' : '0' }}">
    <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
        <div class="modal-header"><div class="operations-modal-heading"><span class="operations-icon"><i class="oi oi-data-transfer-upload" aria-hidden="true"></i></span><div><h2 class="modal-title h5" id="shipment-import-title">{{ __('Import shipment receipts') }}</h2><p>{{ __('Add shipment receipts from your Excel report.') }}</p></div></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button></div>
        <form method="POST" action="{{ route('dashboard.landing.shipments.import') }}" enctype="multipart/form-data" data-once>
            @csrf
            <div class="modal-body">
                @if($errors->shipmentImport->any())<div class="alert alert-danger">{{ $errors->shipmentImport->first() }}</div>@endif
                <p>{{ __('Upload the original Infosas XLSX receipt report. Every detail row becomes a shipment attached to its trip number. Missing trips are created automatically.') }}</p>
                <div class="alert alert-info">{{ __('The title and totals rows are ignored. Existing identical rows are skipped. Missing phones and receipt numbers are allowed. Amounts are copied exactly without recalculation.') }}</div>
                <div class="mb-3"><label for="shipment_excel">{{ __('Receipt report file') }}</label><input id="shipment_excel" type="file" name="file" accept=".xlsx" class="form-control" required><small class="text-muted">{{ __('XLSX, up to 5 MB and 5000 shipments. Values only, no formulas.') }}</small></div>
                <div class="mb-3"><label for="import_currency">{{ __('Currency of shipment amounts') }}</label><select id="import_currency" name="currency" class="form-control"><option value="">{{ __('Unspecified currency') }}</option>@foreach(['EGP','IQD','USD','EUR','SAR','AED','TRY'] as $currency)<option value="{{ $currency }}" @selected(old('currency') === $currency)>{{ $currency }}</option>@endforeach</select><small class="text-muted">{{ __('Choose only if all amounts in this file use the same currency.') }}</small></div>
                <h3 class="h6">{{ __('Defaults for newly created trips only') }}</h3>
                <div class="row">
                    <div class="col-md-6 mb-3"><label for="import_origin">{{ __('Origin') }}</label><input id="import_origin" class="form-control" name="origin" value="{{ old('origin', __('Egypt')) }}" maxlength="191" required></div>
                    <div class="col-md-6 mb-3"><label for="import_destination">{{ __('Destination') }}</label><input id="import_destination" class="form-control" name="destination" value="{{ old('destination', __('Iraq')) }}" maxlength="191" required></div>
                    <div class="col-md-6 mb-3"><label for="import_departure">{{ __('Departure Date') }}</label><input id="import_departure" class="form-control" type="date" name="departure_date" value="{{ old('departure_date') }}"></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button><button class="btn btn-primary">{{ __('Import shipments') }}</button></div>
        </form>
    </div></div>
</div>
