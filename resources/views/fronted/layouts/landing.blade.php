<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings->company_name ?? 'بلاد الرافدين' }} — {{ __('Shipping & Customs Clearance') }}</title>
    <meta name="description" content="{{ __('Shipping, customs clearance and cargo tracking services — Biladalrafidain Logistics.') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('dashboard/assets/vendor/@fortawesome/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('fronted/landing/css/landing.css') }}">
    @if(app()->getLocale() === 'ar')
        <link rel="stylesheet" href="{{ asset('fronted/landing/css/landing-rtl.css') }}">
    @endif
    <link rel="stylesheet" href="{{ asset('fronted/landing/css/logistics.css') }}?v={{ filemtime(public_path('fronted/landing/css/logistics.css')) }}">
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('fronted/landing/css/marketplace.css') }}?v={{ filemtime(public_path('fronted/landing/css/marketplace.css')) }}">
    <link rel="stylesheet" href="{{ asset('fronted/landing/css/phases-one-two.css') }}?v={{ filemtime(public_path('fronted/landing/css/phases-one-two.css')) }}">
</head>
<body class="market-theme">
    @yield('content')

    <script src="{{ asset('fronted/landing/js/landing.js') }}"></script>
</body>
</html>
