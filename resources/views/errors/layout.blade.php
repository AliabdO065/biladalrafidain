<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['ar', 'he'], true) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — {{ __('Biladalrafidain') }}</title>
    <meta name="robots" content="noindex">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('fronted/landing/css/landing.css') }}">
    @if(in_array(app()->getLocale(), ['ar', 'he'], true))
        <link rel="stylesheet" href="{{ asset('fronted/landing/css/landing-rtl.css') }}">
    @endif
    <style>
        body { padding-bottom: 0; }
        .lk-error {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: linear-gradient(135deg, var(--lk-navy) 0%, var(--lk-navy-light) 60%, #072e2f 100%);
            color: #fff;
        }
        .lk-error-top { padding: 22px 0; }
        .lk-error-main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 20px 60px;
        }
        .lk-error-box { max-width: 560px; text-align: center; }
        .lk-error-icon {
            width: 84px; height: 84px; border-radius: 50%;
            margin: 0 auto 10px;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 2.2rem; color: var(--lk-yellow);
        }
        .lk-error-code {
            font-size: clamp(5rem, 22vw, 9rem);
            font-weight: 800;
            line-height: 1;
            margin: 6px 0 4px;
            color: var(--lk-yellow);
            letter-spacing: .02em;
        }
        .lk-error-box h1 { font-size: clamp(1.4rem, 4.5vw, 2rem); margin: 0 0 12px; font-weight: 800; }
        .lk-error-box p { color: rgba(255,255,255,.85); font-size: 1.05rem; margin: 0 0 28px; }
    </style>
</head>
<body>
    <div class="lk-error">
        <div class="lk-error-top">
            <div class="lk-container">
                <a href="{{ url('/') }}" class="lk-logo">
                    <span class="lk-logo-mark"><i class="fa-solid fa-ship"></i></span>
                    {{ __('Biladalrafidain') }}
                </a>
            </div>
        </div>

        <main class="lk-error-main">
            <div class="lk-error-box">
                <div class="lk-error-icon"><i class="fa-solid @yield('icon')"></i></div>
                <div class="lk-error-code">@yield('code')</div>
                <h1>@yield('heading')</h1>
                <p>@yield('message')</p>
                <a href="{{ url('/') }}" class="lk-btn lk-btn-primary">
                    <i class="fa-solid fa-house"></i> {{ __('Back to Home') }}
                </a>
            </div>
        </main>
    </div>
</body>
</html>
