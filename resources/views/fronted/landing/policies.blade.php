@extends('fronted.layouts.landing')

@section('content')
    @include('fronted.landing.partials._navbar')

    <header class="lk-hero" style="padding-bottom:20px;">
        <div class="lk-container">
            <div style="max-width:640px;margin:0 auto;text-align:center;">
                <span class="lk-hero-badge"><i class="fa-solid fa-file-shield"></i> {{ __('Policies') }}</span>
                <h1>{{ __('Company Policies') }}</h1>
            </div>
        </div>
    </header>

    <section class="lk-section">
        <div class="lk-container" style="max-width:760px;">
            @forelse($policies as $policy)
                <div id="{{ $policy->slug }}" class="lk-card" style="padding:24px;margin-bottom:20px;scroll-margin-top:100px;">
                    <h2 class="lk-h2" style="font-size:1.3rem;">{{ $policy->title }}</h2>
                    <p style="white-space:pre-line;color:var(--lk-gray-600,#555);">{{ $policy->body }}</p>
                </div>
            @empty
                <p style="text-align:center;">{{ __('No policies published yet.') }}</p>
            @endforelse
        </div>
    </section>

    @include('fronted.landing.partials._footer')
    @include('fronted.landing.partials._sticky-bar')
@endsection
