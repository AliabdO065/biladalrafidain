@extends('dashboard.layouts.layout')
@section('content')
<div class="wrapper"><div class="page"><div class="page-inner">
<header class="operations-page-header"><div><span class="ops-page-eyebrow">{{ __('flow.overview') }}</span><h1>{{ __('Dashboard') }}</h1><p class="text-muted">{{ __('release.dashboard_hint') }}</p></div><a href="{{ route('dashboard.landing.shipments', ['create'=>1]) }}" class="btn btn-primary"><i class="oi oi-data-transfer-upload" aria-hidden="true"></i>{{ __('Add Shipment') }}</a></header>
<div class="flow-metrics">
@foreach([
['dashboard.trips.index','Active Trips',$tripsCount,'map'],
['dashboard.landing.shipments','Shipments',$shipmentsCount,'box'],
['dashboard.accounts.index','Merchants',$merchantsCount,'people'],
['dashboard.landing.leads','Callback Leads',$leadsCount,'phone'],
] as [$route,$label,$count,$icon])
@continue(!\Illuminate\Support\Facades\Route::has($route))
<a href="{{ route($route,$route === 'dashboard.trips.index' ? ['status'=>'active'] : []) }}" class="card flow-metric"><div class="card-body"><span class="flow-metric-icon oi oi-{{ $icon }}" aria-hidden="true"></span><span>{{ __($label) }}</span><strong>{{ number_format($count) }}</strong><i class="oi oi-arrow-right flow-metric-arrow" aria-hidden="true"></i></div></a>
@endforeach
</div>
<section class="flow-section"><div class="operations-section-header"><h2 class="h5">{{ __('flow.needs_attention') }}</h2></div><div class="flow-attention">
@foreach([
['financial','dashboard.landing.shipments',['attention'=>'financial'],'spreadsheet'],
['contacts','dashboard.trade.contacts',['attention'=>'phone'],'people'],
['failed','dashboard.trade.messages',['status'=>'failed'],'warning'],
['reviews','dashboard.catalog.index',['status'=>'pending'],'grid-three-up'],
] as [$key,$route,$params,$icon])
@continue(!\Illuminate\Support\Facades\Route::has($route))
<a class="card" href="{{ route($route,$params) }}"><div class="card-body"><span class="oi oi-{{ $icon }}" aria-hidden="true"></span><div><strong>{{ __('flow.'.$key) }}</strong><small>{{ $attention[$key] ? __('flow.review') : __('flow.all_clear') }}</small></div><b class="{{ $attention[$key] ? 'flow-pending' : '' }}">{{ $attention[$key] }}</b></div></a>
@endforeach
</div></section>
<div class="flow-overview-grid">
<section class="card"><div class="card-body"><div class="operations-section-header"><h2 class="h5">{{ __('flow.recent_trips') }}</h2><a href="{{ route('dashboard.trips.index') }}">{{ __('market.all') }}</a></div>
@forelse($recentTrips as $trip)
<div class="flow-trip-row"><span class="flow-trip-number">#{{ $trip->reference }}</span><div><strong>{{ $trip->origin }} → {{ $trip->destination }}</strong><small>{{ \App\Support\ShipmentStages::label($trip->status) }} · {{ $trip->shipments_count }} {{ __('Shipments') }}</small></div><a class="btn btn-outline-primary" href="{{ route('dashboard.trips.show',$trip) }}">{{ __('flow.manage') }}</a></div>
@empty<div class="flow-empty"><i class="oi oi-map" aria-hidden="true"></i><p>{{ __('flow.no_trips') }}</p><a href="{{ route('dashboard.trips.index') }}">{{ __('Add Trip') }}</a></div>@endforelse
</div></section>
<section class="card flow-quick"><div class="card-body"><h2 class="h5">{{ __('Quick actions') }}</h2>
@foreach([['dashboard.trade.index','flow.import','flow.step_import','data-transfer-upload'],['dashboard.trips.index','flow.trips','flow.step_trip','map'],['dashboard.accounts.index','flow.accounts','flow.step_accounts','spreadsheet'],['dashboard.trade.messages','flow.messages','flow.step_messages','chat']] as [$route,$label,$hint,$icon])
@continue(!\Illuminate\Support\Facades\Route::has($route))
<a href="{{ route($route) }}"><span class="oi oi-{{ $icon }}" aria-hidden="true"></span><div><strong>{{ __($label) }}</strong><small>{{ __($hint) }}</small></div></a>
@endforeach
</div></section></div>
<div class="flow-guide-banner"><div><span class="oi oi-book" aria-hidden="true"></span><div><h2 class="h5">{{ __('guide.welcome') }}</h2><p>{{ __('guide.welcome_text') }}</p></div></div><a href="{{ route('dashboard.help') }}" class="btn btn-primary">{{ __('guide.open') }}</a></div>
</div></div></div>
@endsection
