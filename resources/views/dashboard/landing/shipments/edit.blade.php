@extends('dashboard.layouts.layout')
@section('content')
<div class="wrapper"><div class="page"><div class="page-inner">
<h1 class="h3">{{ __('Shipment') }}: {{ $item->tracking_code }}</h1>
@include('dashboard.partials.feedback')
<div class="flow-context"><div><strong>{{ $item->receiver_name ?: __('Shipment') }}</strong><small>{{ $item->trip_id ? __('flow.linked_trip') : __('flow.financial_hint') }}</small></div><div class="operations-row-actions">
<a class="btn btn-light" href="{{ route('dashboard.landing.shipments',array_filter(['trip_id'=>$item->trip_id])) }}">{{ __('flow.back_shipments') }}</a>
@if($item->account_key){{-- Phase 3/4: <a class="btn btn-outline-primary" href="{{ route('dashboard.accounts.merchant',array_filter(['key'=>$item->account_key,'bucket'=>$item->currency,'trip_id'=>$item->trip_id])) }}">{{ __('flow.shipping_account') }}</a> --}}@endif
@if($contact){{-- Phase 3/4: <a class="btn btn-light" href="{{ route('dashboard.trade.movements',$contact) }}">{{ __('flow.merchant_file') }}</a> --}}@endif
{{-- Phase 3/4: <a class="btn btn-outline-primary" href="{{ route('dashboard.trade.messages',['shipment_id'=>$item->id]) }}">{{ __('flow.shipment_message') }}</a> --}}
{{-- Phase 3/4: <form method="POST" action="{{ route('dashboard.trade.shipment.prepare',$item) }}" data-once>@csrf<button class="btn btn-primary">{{ __('flow.prepare_shipment') }}</button></form> --}}
{{-- Phase 3/4: <a class="btn btn-light" href="{{ route('dashboard.trade.document',$item) }}" target="_blank" rel="noopener">{{ __('trade.document') }}</a> --}}
</div></div>
<div class="card mb-4"><div class="card-body">
    @include('dashboard.partials.stages', ['status' => $item->current_status])
    @if($item->trip_id)<a href="{{ route('dashboard.trips.show', $item->trip_id) }}" class="btn btn-primary">{{ __('Manage Trip') }}</a>
    @elseif($next = \App\Support\ShipmentStages::next($item->status))
    <form method="POST" action="{{ route('dashboard.landing.shipments.advance', $item) }}" data-once>@csrf<input type="hidden" name="expected_status" value="{{ $item->status }}"><button class="btn btn-primary">{{ __('Move to') }}: {{ \App\Support\ShipmentStages::label($next) }}</button></form>
    @endif
    <p class="mt-3"><a href="{{ route('fronted.tracking', ['code' => $item->tracking_code]) }}" target="_blank" rel="noopener">{{ __('Customer tracking link') }}</a></p>
</div></div>
<div class="card mb-4"><div class="card-body"><form action="{{ route('dashboard.landing.shipments.update', $item) }}" method="POST">
    @csrf
    @include('dashboard.landing.shipments._fields')
    <button class="btn btn-primary">{{ __('Save') }}</button>
</form></div></div>
</div></div></div>
@endsection
