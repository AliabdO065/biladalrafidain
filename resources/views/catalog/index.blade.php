@extends('catalog.layout')
@section('catalog-content')
<header class="catalog-hero"><div><span class="catalog-eyebrow">{{ __('catalog.eyebrow') }}</span><h1>{{ __('catalog.title') }}</h1><p>{{ __('catalog.intro') }}</p></div><span class="catalog-hero-icon fa-solid fa-industry" aria-hidden="true"></span></header>
<form class="catalog-filters" method="GET">
    <div class="catalog-search"><label class="lk-sr-only" for="catalog-query">{{ __('catalog.search') }}</label><input id="catalog-query" name="q" value="{{ $filters['q']??'' }}" placeholder="{{ __('catalog.search') }}" maxlength="100"></div>
    <div><label for="catalog-category">{{ __('catalog.category') }}</label><select id="catalog-category" name="category"><option value="">{{ __('catalog.all') }}</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(($filters['category']??null)==$category->id)>{{ $category->parent ? $category->parent->label.' / ' : '' }}{{ $category->label }}</option>@endforeach</select></div>
    <div><label for="catalog-kind">{{ __('catalog.kind') }}</label><select id="catalog-kind" name="kind"><option value="">{{ __('catalog.all') }}</option>@foreach(['product','work'] as $kind)<option value="{{ $kind }}" @selected(($filters['kind']??null)===$kind)>{{ __('catalog.'.$kind) }}</option>@endforeach</select></div>
    <div><label for="catalog-city">{{ __('catalog.city') }}</label><input id="catalog-city" name="city" value="{{ $filters['city']??'' }}" maxlength="100"></div>
    <button class="catalog-button">{{ __('catalog.filter') }}</button><a href="{{ route('fronted.catalog') }}">{{ __('catalog.clear') }}</a>
</form>
@include('catalog._cards')
<p class="catalog-disclaimer">{{ __('catalog.catalog_note') }}</p>
@endsection
