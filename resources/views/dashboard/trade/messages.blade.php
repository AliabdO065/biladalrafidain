@extends('dashboard.layouts.layout')
@section('content')
<div class="wrapper"><div class="page"><div class="page-inner operations-detail-page">
<header class="operations-page-header"><div><span class="ops-page-eyebrow">{{ __('flow.next') }}</span><h1>{{ __('trade.messages') }}</h1><p class="text-muted">{{ __('trade.message_note') }}</p></div><a class="btn btn-outline-primary" href="{{ route('dashboard.trade.templates') }}">{{ __('trade.templates') }}</a></header>
@include('dashboard.trade._nav')
@if(collect($messageFilters)->except(['phone','status'])->filter()->isNotEmpty())
<div class="flow-context"><div><strong>{{ __('flow.filtered') }}</strong><small>@if($contextTrip){{ __('Trip') }} {{ $contextTrip->reference }} · {{ __('flow.shipment_messages') }}@else{{ __('flow.prepared_only') }}@endif</small></div>
<div class="operations-row-actions">@if($contextTrip)<a class="btn btn-light" href="{{ route('dashboard.trips.show',$contextTrip) }}">{{ __('flow.back_trip') }}</a>@endif<a class="btn btn-outline-primary" href="{{ route('dashboard.trade.messages') }}">{{ __('flow.clear') }}</a></div></div>
@endif
<form method="GET" class="flow-filter card"><div class="card-body">
@foreach(\Illuminate\Support\Arr::except($messageFilters,['phone','status']) as $key=>$value)<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endforeach
<div><label for="messages-phone">{{ __('flow.phone') }}</label><input id="messages-phone" class="form-control" name="phone" value="{{ $phone }}" dir="ltr" placeholder="+964…" maxlength="30"></div>
<div><label for="messages-status">{{ __('flow.status') }}</label><select id="messages-status" class="form-control" name="status"><option value="">{{ __('trade.all') }}</option>@foreach(['draft','queued','processing','accepted','sent','delivered','read','received','failed','unknown','cancelled'] as $value)<option value="{{ $value }}" @selected($value===$status)>{{ __('trade.'.$value) }}</option>@endforeach</select></div>
<button class="btn btn-primary">{{ __('flow.filter') }}</button>
</div></form>
@if($phone)<div class="card mb-4"><div class="card-body"><h2 class="h5">{{ __('trade.reply') }} <span dir="ltr">{{ $phone }}</span></h2>@if($canReply)<form method="POST" action="{{ route('dashboard.trade.reply') }}" data-once>@csrf<input type="hidden" name="phone" value="{{ $phone }}"><input type="hidden" name="nonce" value="{{ \Illuminate\Support\Str::uuid() }}"><textarea class="form-control mb-3" name="body" rows="3" maxlength="4000" required placeholder="{{ __('trade.reply_text') }}"></textarea><button class="btn btn-primary">{{ __('trade.reply_send') }}</button></form>@else<p class="text-muted">{{ __('trade.window_closed') }}</p>@endif</div></div>@endif
@forelse($messages as $message)
<article class="card mb-3 flow-message" data-direction="{{ $message->direction }}"><div class="card-body">
<div class="flow-message-heading"><div><strong>{{ $message->contact?->name ?: $message->phone ?: __('trade.review_phone') }}</strong>@if($message->phone)<a class="mx-2" dir="ltr" href="{{ route('dashboard.trade.messages',['phone'=>$message->phone]) }}">{{ $message->phone }}</a>@endif<small class="text-muted">{{ __('trade.'.$message->direction) }} · {{ $message->created_at }}</small></div><span class="flow-status" data-status="{{ $message->status }}">{{ __('trade.'.$message->status) }}</span></div>
<p dir="auto" class="flow-message-body">{{ $message->body ?: '—' }}</p>
@if($message->error)<p class="text-warning" dir="ltr">{{ $message->error }}</p>@endif
<div class="flow-source"><span>{{ __('flow.source') }}:</span>
@if($message->source_type==='shipment' && $source=$message->shipmentSource)
<a href="{{ route('dashboard.landing.shipments.edit',$source) }}">{{ $source->tracking_code }}</a>
@if($source->trip)<a href="{{ route('dashboard.trips.show',$source->trip) }}">{{ __('Trip') }} {{ $source->trip->reference }}</a>@endif
@elseif($message->source_type==='voucher' && $source=$message->voucherSource)
<a href="{{ route('dashboard.trade.preview',$source->import_id) }}">{{ __('trade.reference') }} {{ $source->reference }}</a>
@elseif($message->source_type)<span>{{ __('flow.no_source') }}</span>
@else<span>{{ __('trade.conversation') }}</span>@endif
@if($message->contact)<a href="{{ route('dashboard.trade.movements',$message->contact) }}">{{ __('flow.merchant_file') }}</a>@endif
</div>
<div class="operations-row-actions">
@if($message->direction==='out' && $message->kind!=='reply' && in_array($message->status,['draft','failed'],true))
<form action="{{ route('dashboard.trade.messages.send',$message) }}" method="POST" data-once>@csrf<button class="btn btn-outline-primary">{{ __('trade.send') }}</button></form>
<form action="{{ route('dashboard.trade.messages.refresh',$message) }}" method="POST" data-once>@csrf<button class="btn btn-light">{{ __('trade.refresh') }}</button></form>
@endif
@if($message->direction==='out' && in_array($message->status,['draft','failed','queued'],true))<form action="{{ route('dashboard.trade.messages.cancel',$message) }}" method="POST" data-once>@csrf<button class="btn btn-light">{{ __('trade.cancel') }}</button></form>@endif
@if($message->source_type==='shipment' && $message->shipmentSource)<a class="btn btn-light" href="{{ route('dashboard.trade.document',$message->source_id) }}" target="_blank" rel="noopener">{{ __('trade.document') }}</a>@endif
</div></div></article>
@empty<div class="flow-empty"><i class="oi oi-chat" aria-hidden="true"></i><h2 class="h5">{{ __('flow.empty_messages') }}</h2><a href="{{ route('dashboard.trade.index') }}">{{ __('flow.back_import') }}</a></div>@endforelse
<p class="text-muted small">{{ __('trade.media_note') }}</p><div class="operations-pagination">{{ $messages->links() }}</div>
</div></div></div>
@endsection