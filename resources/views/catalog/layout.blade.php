@extends('fronted.layouts.landing')
@push('styles')
<link rel="stylesheet" href="{{ asset('fronted/landing/css/catalog.css') }}?v={{ filemtime(public_path('fronted/landing/css/catalog.css')) }}">
@endpush
@section('content')
@include('fronted.landing.partials._navbar')
<main class="catalog-shell">
    <div class="lk-container">
        <nav class="catalog-topnav" aria-label="{{ __('catalog.title') }}">
            <a href="{{ route('fronted.catalog') }}">{{ __('catalog.title') }}</a>
            <div>
                <a href="{{ route('fronted.catalog.factories') }}">{{ __('catalog.factories') }}</a>
                @if(Auth::guard('factory')->check())
                    <a href="{{ route('factory.home') }}">{{ __('catalog.portal') }}</a>
                    <form method="POST" action="{{ route('factory.logout') }}">@csrf<button>{{ __('catalog.logout') }}</button></form>
                @else
                    <a href="{{ route('factory.login') }}">{{ __('catalog.factory_login') }}</a><a class="catalog-nav-cta" href="{{ route('factory.register') }}">{{ __('catalog.factory_register') }}</a>
                @endif
            </div>
        </nav>
        @if($errors->any())<div class="catalog-notice catalog-notice--error" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @if(session('success'))<div class="catalog-notice" role="status">{{ session('success') }}</div>@endif
        @yield('catalog-content')
    </div>
</main>
@include('fronted.landing.partials._footer')
@endsection
