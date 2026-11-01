@extends('catalog.layout')
@section('catalog-content')
<header class="catalog-hero"><div><span class="catalog-eyebrow">{{ __('catalog.factory_page') }}</span><h1>{{ $factory->name }}</h1><p>{{ $factory->activity }}@if($factory->city) · {{ $factory->city }}@endif</p></div><a class="catalog-button catalog-button--gold" href="tel:{{ preg_replace('/[^0-9+]/','',$factory->phone) }}">{{ __('catalog.contact') }}</a></header>
@if($factory->description)<p class="catalog-about">{{ $factory->description }}</p>@endif
@include('catalog._cards')<p class="catalog-disclaimer">{{ __('catalog.catalog_note') }}</p>
@endsection
