<div class="row">
    <div class="col-md-6 mb-3"><label for="shipment_trip_id">{{ __('Trip') }}</label>
        <select id="shipment_trip_id" name="trip_id" class="form-control"><option value="">{{ __('Independent shipment') }}</option>
            @foreach($trips as $trip)<option value="{{ $trip->id }}" @selected((string) old('trip_id', isset($item) ? $item->trip_id : request('trip_id', $selectedTripId ?? '')) === (string) $trip->id)>{{ $trip->reference }} — {{ $trip->origin }} / {{ $trip->destination }}</option>@endforeach
        </select><small class="text-muted">{{ __('All shipments on this trip follow its current stage.') }}</small>
    </div>
    <div class="col-md-6 mb-3"><label for="shipment_tracking_code">{{ __('Tracking Code') }}</label><input id="shipment_tracking_code" class="form-control" name="tracking_code" value="{{ old('tracking_code', isset($item) ? $item->tracking_code : '') }}" maxlength="191" @if(isset($item)) required @endif>
        @if(!isset($item))<small class="text-muted">{{ __('Leave blank to generate a private tracking code.') }}</small>@endif
    </div>
    @foreach(\App\Support\ShipmentFields::labels() as $field => $label)
        <div class="{{ $field === 'notes' ? 'col-12' : 'col-md-4' }} mb-3"><label for="shipment_{{ $field }}">{{ $label }}</label>
            @if($field === 'notes')<textarea id="shipment_{{ $field }}" name="{{ $field }}" class="form-control" rows="3" maxlength="5000">{{ old($field, isset($item) ? $item->$field : '') }}</textarea>
            @elseif($field === 'currency')<select id="shipment_currency" name="currency" class="form-control"><option value="">{{ __('Unspecified currency') }}</option>@foreach(['EGP','IQD','USD','EUR','SAR','AED','TRY'] as $currency)<option value="{{ $currency }}" @selected(old('currency', isset($item) ? $item->currency : '') === $currency)>{{ $currency }}</option>@endforeach</select>
            @elseif($field === 'receipt_date')<input id="shipment_{{ $field }}" type="date" class="form-control" name="{{ $field }}" value="{{ old($field, isset($item) ? optional($item->receipt_date)->format('Y-m-d') : '') }}">
            @elseif(in_array($field, ['packages_count', 'weight_kg', 'item_price', 'shipping_fee', 'paid_amount', 'balance', 'total']))
                <input id="shipment_{{ $field }}" type="number" step="{{ $field === 'packages_count' ? '1' : ($field === 'weight_kg' ? '0.001' : ($field === 'item_price' ? '0.0001' : '0.01')) }}" @if(in_array($field, ['packages_count', 'weight_kg'])) min="0" @endif class="form-control" name="{{ $field }}" value="{{ old($field, isset($item) ? $item->$field : '') }}">
            @else<input id="shipment_{{ $field }}" type="{{ $field === 'receiver_phone' ? 'tel' : 'text' }}" class="form-control" name="{{ $field }}" value="{{ old($field, isset($item) ? $item->$field : '') }}" maxlength="191">@endif
        </div>
    @endforeach
    @foreach(['origin' => __('Origin'), 'destination' => __('Destination')] as $field => $label)
        <div class="col-md-4 mb-3"><label for="shipment_{{ $field }}">{{ $label }}</label><input id="shipment_{{ $field }}" name="{{ $field }}" class="form-control" value="{{ old($field, isset($item) ? $item->$field : '') }}" maxlength="191"></div>
    @endforeach
    <div class="col-md-4 mb-3"><label for="shipment_estimated_delivery">{{ __('Estimated Delivery') }}</label><input id="shipment_estimated_delivery" type="date" class="form-control" name="estimated_delivery" value="{{ old('estimated_delivery', isset($item) ? optional($item->estimated_delivery)->format('Y-m-d') : '') }}"></div>
</div>
<input type="hidden" name="status" value="{{ isset($item) ? $item->status : 'received' }}">
