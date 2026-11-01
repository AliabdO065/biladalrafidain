@extends('catalog.layout')
@section('catalog-content')
<div class="catalog-detail">
    <div class="catalog-gallery">@foreach($item->photos as $photo)<a href="{{ route('fronted.catalog.photo',$photo) }}" target="_blank" rel="noopener"><img src="{{ route('fronted.catalog.photo',$photo) }}" alt="{{ $item->title }} · {{ $loop->iteration }}" @if(!$loop->first) loading="lazy" @endif></a>@endforeach</div>
    <article class="catalog-information"><span class="catalog-eyebrow">{{ $item->category->label }} · {{ __('catalog.'.$item->kind) }}</span><h1>{{ $item->title }}</h1><a class="catalog-maker" href="{{ route('fronted.catalog.factory',$item->factory_id) }}">{{ $item->factory->name }}</a><p class="catalog-about">{{ $item->description }}</p><strong class="catalog-price">{{ $item->price !== null ? $item->price.' '.$item->currency : __('catalog.ask_price') }}</strong>@if($item->minimum_order)<p>{{ __('catalog.minimum_order') }}: {{ $item->minimum_order }}</p>@endif
    <div class="catalog-contact-card"><h2>{{ $item->factory->name }}</h2><p>{{ $item->factory->city }} · {{ $item->factory->activity }}</p><a class="catalog-button" href="tel:{{ preg_replace('/[^0-9+]/','',$item->factory->phone) }}">{{ __('catalog.contact') }}</a><a href="{{ route('fronted.catalog.factory',$item->factory_id) }}">{{ __('catalog.factory_page') }}</a></div><p class="catalog-disclaimer">{{ __('catalog.catalog_note') }}</p></article>
</div>
@endsection
