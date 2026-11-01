<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0e4a4d">
    <title>{{ __('Login') }} — {{ __('Biladalrafidain') }}</title>
    <link rel="stylesheet" href="{{ asset('dashboard/assets/stylesheets/login.css') }}?v={{ filemtime(public_path('dashboard/assets/stylesheets/login.css')) }}">
    <script src="{{ asset('dashboard/assets/javascript/login.js') }}?v={{ filemtime(public_path('dashboard/assets/javascript/login.js')) }}" defer></script>
</head>
<body>
    <div class="auth-page">
        <header class="auth-header">
            <a class="auth-brand" href="{{ route('fronted.index') }}" aria-label="{{ __('Biladalrafidain') }}">
                <span class="auth-brand-mark">@include('auth._icon', ['icon' => 'ship'])</span>
                <span><strong>{{ __('Biladalrafidain') }}</strong><small>{{ __('Shipping & Logistics') }}</small></span>
            </a>
            <nav class="auth-languages" aria-label="{{ __('Languages') }}">
                @foreach(['ar' => 'العربية', 'en' => 'English'] as $code => $label)
                    <a href="{{ route('login.setLocale', $code) }}" lang="{{ $code }}" @if(app()->getLocale() === $code) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
            </nav>
        </header>
        <main class="auth-shell">
            @yield('content')
        </main>
        <footer class="auth-footer">
            <span>© {{ now()->year }} {{ __('Biladalrafidain') }}. {{ __('All Rights Reserved') }}</span>
            <span>{{ __('Shipping & Customs Clearance') }}</span>
        </footer>
    </div>
</body>
</html>
