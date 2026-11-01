<div class="ops-row-actions">
    {{-- Phase 3/4: <a href="{{ route('dashboard.trade.document', $item->id) }}" class="ops-action-icon" target="_blank" rel="noopener" title="{{ __('trade.document') }}" aria-label="{{ __('trade.document') }}"><i class="oi oi-document" aria-hidden="true"></i></a> --}}
    <a href="{{ route('dashboard.landing.shipments.edit', $item->id) }}" class="ops-action-main" title="{{ __('Edit') }}"><i class="oi oi-pencil" aria-hidden="true"></i>{{ __('Edit') }}</a>
    <a href="{{ route('fronted.tracking', ['code' => $item->tracking_code]) }}" class="ops-action-icon" target="_blank" rel="noopener" title="{{ __('Track') }}" aria-label="{{ __('Track') }}"><i class="oi oi-location" aria-hidden="true"></i></a>
    <form method="POST" action="{{ route('dashboard.landing.shipments.delete', $item->id) }}" onsubmit="return confirm(@js(__('Delete this shipment?')))">@csrf<button class="ops-action-icon ops-action-danger" title="{{ __('Löschen') }}" aria-label="{{ __('Löschen') }}"><i class="oi oi-trash" aria-hidden="true"></i></button></form>
</div>
