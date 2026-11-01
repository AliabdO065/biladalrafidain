@extends('fronted.layouts.landing')
@section('content')
@include('fronted.landing.partials._navbar')
@include('fronted.landing.partials._alert-banner')
<main class="mk-home">
<section class="mk-hero">
<div class="mk-hero-copy"><span class="mk-route">@include('fronted.landing.partials._flag',['country'=>'egypt']) <span>{{ __('release.tagline') }}</span> @include('fronted.landing.partials._flag',['country'=>'iraq'])</span>
<h1>{{ $settings->hero_headline ?? __('market.headline') }}</h1>
<p>{{ $settings->hero_subheadline ?? __('market.subheadline') }}</p></div>
<div class="mk-actions">
{{-- Phase 4 catalogue actions retained.
@foreach([['fronted.catalog','boxes','browse','browse_hint'],['factory.register','industry','register','factory_hint'],['fronted.tracking','map-marker-alt','track','track_hint'],['fronted.index','truck','shipping','shipping_hint']] as [$link,$icon,$label,$hint])
<a class="mk-action" href="{{ route($link) }}{{ $label === 'shipping' ? '#lk-callback' : '' }}"><i class="fas fa-{{ $icon }}" aria-hidden="true"></i><span><strong>{{ __('market.'.$label) }}</strong><small>{{ __('market.'.$hint) }}</small></span></a>
@endforeach
--}}
<a class="mk-action" href="{{ route('fronted.tracking') }}"><i class="fas fa-map-marker-alt" aria-hidden="true"></i><span><strong>{{ __('market.track') }}</strong><small>{{ __('market.track_hint') }}</small></span></a>
<a class="mk-action" href="#lk-callback"><i class="fas fa-truck" aria-hidden="true"></i><span><strong>{{ __('market.shipping') }}</strong><small>{{ __('market.shipping_hint') }}</small></span></a>
</div></section>
{{-- Phase 4 categories and marketplace metrics.
<section class="mk-categories" aria-label="{{ __('market.categories') }}">
@foreach($categories as $category)
<a href="{{ route('fronted.catalog',['category'=>$category->id]) }}"><span class="mk-category-art"><i class="fas fa-{{ ['tshirt','couch','blender','tools','boxes'][$loop->index % 5] }}" aria-hidden="true"></i></span><strong>{{ $category->label }}</strong><small>{{ __('market.browse') }}</small></a>
@endforeach
</section>
<section class="mk-stats" aria-label="{{ __('market.public') }}">
@foreach([['industry',number_format($marketCounts['factories']),'approved_factories'],['boxes',number_format($marketCounts['products']),'published_products'],['layer-group',number_format($marketCounts['categories']),'categories'],['truck',count(\App\Support\ShipmentStages::labels()),'stages'],['globe-africa','EG ↔ IQ','route']] as [$icon,$value,$label])
<div><i class="fas fa-{{ $icon }}" aria-hidden="true"></i><span><strong>{{ $value }}</strong><small>{{ __('market.'.$label) }}</small></span></div>
@endforeach
</section>

--}}
<div class="mk-features">
<section class="mk-feature" id="shipping"><div class="mk-section-heading"><h2>{{ __('market.shipping_services') }}</h2><a href="#lk-callback">{{ __('market.contact') }} <i class="fas fa-arrow-left" aria-hidden="true"></i></a></div>
<div class="mk-service-grid">
@foreach(['road','customs'] as $service)
<article class="mk-card mk-service"><div class="mk-service-photo {{ $service }}"><span><i class="fas fa-{{ $service === 'road' ? 'truck' : 'clipboard-check' }}" aria-hidden="true"></i></span></div><div class="mk-card-body"><h3>{{ __('market.'.$service) }}</h3><p>{{ __('market.'.$service.'_text') }}</p><a class="mk-card-link" href="#lk-callback">{{ __('market.contact') }}</a></div></article>
@endforeach
</div></section>
{{-- Phase 4 products and factories.
<section class="mk-feature"><div class="mk-section-heading"><h2>{{ __('market.featured_products') }}</h2><a href="{{ route('fronted.catalog') }}">{{ __('market.all') }} <i class="fas fa-arrow-left" aria-hidden="true"></i></a></div>
<div class="mk-product-grid">
@forelse($products as $item)
<article class="mk-card"><a class="mk-product-photo" href="{{ route('fronted.catalog.item',$item) }}">@if($photo=$item->photos->first())<img src="{{ route('fronted.catalog.photo',$photo) }}" alt="{{ $item->title }}" width="300" height="240" loading="lazy">@else<i class="fas fa-box-open" aria-hidden="true"></i>@endif</a><div class="mk-card-body"><small>{{ $item->category->label }}</small><h3><a href="{{ route('fronted.catalog.item',$item) }}">{{ $item->title }}</a></h3><p>{{ $item->factory->name }}</p><strong class="mk-price">{{ $item->price !== null ? $item->price.' '.$item->currency : __('catalog.ask_price') }}</strong><a class="mk-card-link" href="{{ route('fronted.catalog.item',$item) }}">{{ __('market.details') }}</a></div></article>
@empty
<div class="mk-empty"><i class="fas fa-tshirt" aria-hidden="true"></i><h3>{{ __('market.product_empty') }}</h3><p>{{ __('market.product_empty_hint') }}</p><a href="{{ route('fronted.catalog') }}">{{ __('market.browse') }} ←</a></div>
@endforelse
</div></section>
<section class="mk-feature"><div class="mk-section-heading"><h2>{{ __('market.featured_factories') }}</h2><a href="{{ route('fronted.catalog.factories') }}">{{ __('market.all') }} <i class="fas fa-arrow-left" aria-hidden="true"></i></a></div>
<div class="mk-factory-grid">
@forelse($factories as $factory)
<article class="mk-card"><a class="mk-factory-photo" href="{{ route('fronted.catalog.factory',$factory) }}">@if($cover=$factoryCovers->get($factory->id))<img src="{{ route('fronted.catalog.photo',$cover) }}" alt="{{ $factory->name }}" width="300" height="240" loading="lazy">@else<i class="fas fa-industry" aria-hidden="true"></i>@endif<span>{{ __('market.approved_factories') }}</span></a><div class="mk-card-body"><h3><a href="{{ route('fronted.catalog.factory',$factory) }}">{{ $factory->name }}</a></h3><p>{{ $factory->city }} · {{ $factory->activity }}</p><a class="mk-card-link" href="{{ route('fronted.catalog.factory',$factory) }}">{{ __('market.details') }}</a></div></article>
@empty
<div class="mk-empty"><i class="fas fa-industry" aria-hidden="true"></i><h3>{{ __('market.factory_empty') }}</h3><p>{{ __('market.factory_empty_hint') }}</p><a href="{{ route('factory.register') }}">{{ __('market.register') }} ←</a></div>
@endforelse
</div></section>
--}}
</div>
{{-- Phase 4 marketplace instructions.
<section class="mk-how"><h2>{{ __('market.how') }}</h2><div class="mk-steps">
@foreach(['search','industry','comments','truck','map-marker-alt'] as $icon)
<div><b>{{ $loop->iteration }}</b><i class="fas fa-{{ $icon }}" aria-hidden="true"></i><strong>{{ __('market.step'.$loop->iteration) }}</strong><small>{{ __('market.step'.$loop->iteration.'_text') }}</small></div>
@endforeach
</div></section>
<section class="mk-trust">
@foreach(['shield-alt','handshake','map-marker-alt','headset'] as $icon)<div><i class="fas fa-{{ $icon }}" aria-hidden="true"></i><span><strong>{{ __('market.trust'.$loop->iteration) }}</strong><small>{{ __('market.trust'.$loop->iteration.'_text') }}</small></span></div>@endforeach
</section>

--}}
@include('fronted.landing.partials._callback-form')
</main>
@include('fronted.landing.partials._footer')
@endsection
