<header class="mk-header">
<div class="mk-top">
<a href="{{ route('fronted.index') }}" class="mk-brand">
@if($settings->logo_image ?? null)<img src="{{ asset($settings->logo_image) }}" alt="{{ $settings->company_name }}" width="48" height="54">
@else<svg width="48" height="54" viewBox="0 0 48 54" aria-hidden="true"><path fill="#e5b544" d="M24 1 46 13 24 26 2 13z"/><path fill="#082744" d="m2 16 20 12v25L2 41z"/><path fill="#b98721" d="m26 28 20-12v25L26 53z"/><path fill="#fff" opacity=".6" d="m24 1 9 6-9 19-9-6z"/></svg>@endif
<span><strong>{{ $settings->navbar_brand_text ?? __('market.brand') }}</strong><small>BILAD AL RAFIDAIN</small><em>{{ __('release.tagline') }}</em></span>
</a>
{{-- Phase 4: <form class="mk-search" method="GET" action="{{ route('fronted.catalog') }}" role="search">
<input type="search" name="q" maxlength="100" value="{{ request()->query('q') }}" placeholder="{{ __('market.search') }}" aria-label="{{ __('market.search') }}">
<button type="submit" aria-label="{{ __('market.search_button') }}"><i class="fas fa-search" aria-hidden="true"></i></button>
</form> --}}
<div class="mk-countries" aria-label="{{ __('market.route') }}"><span>@include('fronted.landing.partials._flag',['country'=>'egypt']) {{ __('market.egypt') }}</span><b>↔</b><span>@include('fronted.landing.partials._flag',['country'=>'iraq']) {{ __('market.iraq') }}</span></div>
<div class="mk-account">
@if(isset($enabledLanguages) && $enabledLanguages->count() > 1)
<div class="lk-lang-switch"><button type="button" class="lk-lang-current"><i class="fas fa-globe" aria-hidden="true"></i> {{ app()->getLocale() === 'ar' ? 'العربية' : 'English' }}</button><ul class="lk-lang-menu">@foreach($enabledLanguages as $lang)<li><a href="{{ route('fronted.setLocale', $lang->code) }}" lang="{{ $lang->code }}">{{ $lang->native_name }}</a></li>@endforeach</ul></div>
@endif
@if(Auth::guard('factory')->check())
{{-- Phase 3/4: <a class="mk-button" href="{{ route('factory.home') }}"><i class="fas fa-user" aria-hidden="true"></i>{{ __('market.portal') }}</a> --}}
@else
{{-- Phase 3/4: <a class="mk-button mk-navy" href="{{ route('factory.login') }}"><i class="far fa-user" aria-hidden="true"></i>{{ __('market.login') }}</a> --}}
{{-- Phase 3/4: <a class="mk-button" href="{{ route('factory.register') }}"><i class="fas fa-user-plus" aria-hidden="true"></i>{{ __('market.register') }}</a> --}}
@endif
</div></div>
<nav class="mk-nav" aria-label="{{ __('market.home') }}"><div>
<a @class(['active'=>request()->routeIs('fronted.index')]) href="{{ route('fronted.index') }}">{{ __('market.home') }}</a>
{{-- Phase 3/4: <a @class(['active'=>request()->routeIs('fronted.catalog.factories','fronted.catalog.factory')]) href="{{ route('fronted.catalog.factories') }}">{{ __('market.factories') }}</a> --}}
{{-- Phase 3/4: <a @class(['active'=>request()->routeIs('fronted.catalog','fronted.catalog.item')]) href="{{ route('fronted.catalog') }}">{{ __('market.products') }}</a> --}}
<a href="{{ route('fronted.index') }}#shipping">{{ __('market.shipping') }}</a>
@if($settings->nav_show_tracking ?? true)<a @class(['active'=>request()->routeIs('fronted.tracking')]) href="{{ route('fronted.tracking') }}">{{ __('market.track') }}</a>@endif
@if($settings->nav_show_callback ?? true)<a href="{{ route('fronted.index') }}#lk-callback">{{ __('market.help') }}</a>@endif
@if($settings->nav_show_policies ?? true)<a href="{{ route('fronted.policies') }}">{{ __('Policies') }}</a>@endif
</div></nav></header>
