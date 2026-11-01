@extends('dashboard.layouts.layout')
@push('styles')
<link rel="stylesheet" href="{{ asset('dashboard/assets/stylesheets/guide.css') }}?v={{ filemtime(public_path('dashboard/assets/stylesheets/guide.css')) }}">
@endpush
@section('content')
{{-- Deferred guide chapters remain in the translation files with stable IDs. --}}
@php($sections = array_filter(__('guide.sections'), fn ($section, $key) => in_array($key, config('guide.chapters'), true), ARRAY_FILTER_USE_BOTH))
<div class="wrapper"><div class="page"><div class="page-inner help-guide" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" data-guide>
    <header class="guide-hero">
        <div class="guide-hero-copy"><span class="guide-eyebrow"><span class="oi oi-book" aria-hidden="true"></span> {{ __('Dashboard') }}</span>
            <h1>{{ __('guide.title') }}</h1><p>{{ __('guide.subtitle') }}</p><p>{{ __('release.guide_notice') }}</p><p class="guide-browse-hint">{{ __('guide.browse_hint') }}</p>
            <span class="guide-updated">{{ __('guide.updated') }}: <time datetime="{{ config('guide.updated_at') }}">{{ config('guide.updated_at') }}</time></span>
        </div>
        <div class="guide-search">
            <label for="guide-search">{{ __('guide.search_label') }}</label>
            <input type="search" id="guide-search" placeholder="{{ __('guide.search') }}" autocomplete="off" aria-controls="guide-chapters">
            <div class="guide-search-meta"><span role="status" aria-live="polite" aria-atomic="true"><span data-guide-results hidden><span data-guide-count>{{ count($sections) }}</span> {{ __('guide.results') }}</span></span><button type="button" data-guide-clear hidden>{{ __('guide.clear') }}</button></div>
        </div>
    </header>
    <div class="guide-layout">
        <nav class="guide-nav" aria-label="{{ __('guide.contents') }}"><h2>{{ __('guide.contents') }}</h2>
            @foreach($sections as $key => $section)
                <a href="#guide-{{ $key }}" data-guide-link="{{ $key }}"><span class="guide-nav-number">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>{{ $section['title'] }}</a>
            @endforeach
        </nav>
        <div id="guide-chapters" class="guide-chapters">
            <p class="guide-empty" data-guide-empty hidden>{{ __('guide.empty') }}</p>
            @foreach($sections as $key => $section)
            <section class="guide-chapter" id="guide-{{ $key }}" data-guide-section="{{ $key }}" aria-labelledby="guide-title-{{ $key }}">
                <header class="guide-chapter-heading"><span class="guide-chapter-icon oi oi-{{ $section['icon'] }}" aria-hidden="true"></span><div><span class="guide-chapter-number">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><h2 id="guide-title-{{ $key }}" tabindex="-1">{{ $section['title'] }}</h2></div></header>
                <p class="guide-intro">{{ $section['intro'] }}</p>
                <h3>{{ __('guide.steps') }}</h3><ol class="guide-steps">@foreach($section['steps'] as $step)<li>{{ $step }}</li>@endforeach</ol>
                @if($key === 'trips')
                    <h3>{{ __('guide.stages') }}</h3><ol class="guide-stages">@foreach(\App\Support\ShipmentStages::labels() as $label)<li>{{ $label }}</li>@endforeach</ol>
                @endif
                @if($key === 'import')
                    <details class="guide-columns"><summary>{{ __('guide.headers') }}</summary><ul>@foreach(array_keys(\App\Services\ShipmentSpreadsheetImporter::HEADERS) as $heading)<li lang="ar" dir="rtl">{{ $heading }}</li>@endforeach</ul></details>
                @endif
                <aside class="guide-note"><h3><span class="oi oi-info" aria-hidden="true"></span> {{ __('guide.notes') }}</h3><ul>@foreach($section['notes'] as $note)<li>{{ $note }}</li>@endforeach</ul></aside>
                <h3>{{ __('guide.faq') }}</h3><div class="guide-faq">@foreach($section['faq'] as $question => $answer)<details><summary>{{ $question }}</summary><p>{{ $answer }}</p></details>@endforeach</div>
                <a class="guide-open" href="{{ route($section['route']) }}">{{ __('guide.go') }} <span class="oi oi-external-link" aria-hidden="true"></span></a>
            </section>
            @endforeach
        </div>
    </div>
</div></div></div>
@endsection
@push('scripts')
<script src="{{ asset('dashboard/assets/javascript/guide.js') }}?v={{ filemtime(public_path('dashboard/assets/javascript/guide.js')) }}" defer></script>
@endpush
