@php
$workspaceLinks = [
['dashboard.trade.index','flow.import','data-transfer-upload','dashboard.trade.index','dashboard.trade.preview'],
['dashboard.trips.index','flow.trips','map','dashboard.trips.*'],
['dashboard.landing.shipments','flow.shipments','box','dashboard.landing.shipments*'],
['dashboard.accounts.index','flow.accounts','spreadsheet','dashboard.accounts.*'],
['dashboard.trade.messages','flow.messages','chat','dashboard.trade.messages'],
];
@endphp
<div class="workspace-bar account-no-print">
<div class="workspace-heading"><a href="{{ route('dashboard.index') }}"><span class="oi oi-home" aria-hidden="true"></span> {{ __('flow.workspace') }}</a><span class="workspace-divider">/</span><span>{{ __('flow.workflow') }}</span><div class="workspace-utilities"><a href="{{ route('dashboard.help') }}"><i class="oi oi-book" aria-hidden="true"></i> {{ __('guide.title') }}</a><a href="{{ route('fronted.index') }}" target="_blank" rel="noopener"><i class="oi oi-external-link" aria-hidden="true"></i> {{ __('flow.view_site') }}</a></div></div>
<nav class="workspace-steps" aria-label="{{ __('flow.workflow') }}">@foreach($workspaceLinks as $step)
@continue(!\Illuminate\Support\Facades\Route::has($step[0]))
@php($active=request()->routeIs(...array_slice($step,3)))
<a href="{{ route($step[0]) }}" @if($active) aria-current="page" @endif><span class="workspace-step-number">{{ $loop->iteration }}</span><i class="oi oi-{{ $step[2] }}" aria-hidden="true"></i><strong>{{ __($step[1]) }}</strong></a>
@endforeach</nav>
</div>