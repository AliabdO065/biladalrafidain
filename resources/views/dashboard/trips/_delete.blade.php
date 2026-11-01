@php($tripHasShipments = ($tripShipmentCount ?? $trip->shipments_count ?? 0) > 0)
<form class="ops-trip-delete-form" method="POST" action="{{ route('dashboard.trips.destroy', $trip) }}" title="{{ $tripHasShipments ? __('Move or delete this trip shipments before deleting the trip.') : __('Delete trip') }}" onsubmit="return confirm(@js(__('Delete trip :reference? This removes the trip and its tracking history.', ['reference' => $trip->reference])))">
    @csrf
    @method('DELETE')
    <button type="submit" class="{{ $deleteClass ?? 'ops-action-icon ops-action-danger' }}" aria-label="{{ __('Delete trip') }}" @disabled($tripHasShipments)><i class="oi oi-trash" aria-hidden="true"></i>@if($showDeleteLabel ?? false) {{ __('Delete trip') }}@endif</button>
</form>
