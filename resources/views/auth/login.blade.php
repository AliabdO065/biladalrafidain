@extends('dashboard.layouts.app')
@section('content')
<aside class="auth-visual" aria-labelledby="auth-visual-title">
    <div class="auth-eyebrow">{{ __('Connecting Egypt and Iraq') }}</div>
    <h1 id="auth-visual-title">{{ __('Every shipment.') }}<span>{{ __('One clear view.') }}</span></h1>
    <p class="auth-visual-intro">{{ __('Your trips, shipments and merchant accounts, together in one place. A clearer view of every step.') }}</p>
    <div class="auth-route" aria-hidden="true">
        <svg viewBox="0 0 430 180" fill="none"><path d="M40 133C120 155 132 59 212 83S300 105 387 41" stroke="#ffffff0b" stroke-width="24"/><path class="auth-route-line" d="M40 133C120 155 132 59 212 83S300 105 387 41" stroke="#d5b26e" stroke-opacity=".6" stroke-width="1.5"/><circle cx="40" cy="133" r="4" fill="#d5aa55"/><circle cx="387" cy="41" r="4" fill="#d5aa55"/></svg>
        <div class="auth-route-node">@include('auth._icon', ['icon' => 'ship'])<div><strong>{{ __('Egypt') }}</strong><small>{{ __('The journey begins') }}</small></div></div>
        <div class="auth-route-node">@include('auth._icon', ['icon' => 'route'])<div><strong>{{ __('Iraq') }}</strong><small>{{ __('To your destination') }}</small></div></div>
        <span class="auth-route-parcel">@include('auth._icon', ['icon' => 'box'])</span>
    </div>
    <div class="auth-visual-bottom">
        <span class="auth-feature">@include('auth._icon', ['icon' => 'route']){{ __('Trips') }}</span>
        <span class="auth-feature">@include('auth._icon', ['icon' => 'box']){{ __('Shipments') }}</span>
        <span class="auth-feature">@include('auth._icon', ['icon' => 'account']){{ __('Merchant Accounts') }}</span>
    </div>
</aside>
<section class="auth-form-panel" aria-labelledby="auth-form-title">
    <div class="auth-form-inner">
        <div class="auth-form-badge">@include('auth._icon', ['icon' => 'lock']){{ __('Management Portal') }}</div>
        <h2 id="auth-form-title">{{ __('Welcome back') }}</h2>
        <p class="auth-form-intro">{{ __('Sign in to manage your shipments and keep your business moving.') }}</p>
        @if($errors->any())
            <div class="auth-error-summary" role="alert">
                @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif
        <form id="auth-login-form" method="POST" action="{{ route('login') }}" data-ready-label="{{ __('Sign in to dashboard') }}" data-loading-label="{{ __('Signing you in…') }}">
            @csrf
            <div class="auth-field">
                <label for="email">{{ __('Email Address') }}</label>
                <div class="auth-input-wrap">
                    @include('auth._icon', ['icon' => 'mail', 'class' => 'auth-input-icon'])
                    <input class="auth-input auth-email" id="email" type="email" name="email" value="{{ old('email') }}" placeholder="name@company.com" required autocomplete="username" inputmode="email" spellcheck="false" autocapitalize="none" @error('email') aria-invalid="true" aria-describedby="email-error" autofocus @enderror>
                </div>
                @error('email')<span id="email-error" class="auth-field-error">{{ $message }}</span>@enderror
            </div>
            <div class="auth-field">
                <label for="password">{{ __('Password') }}</label>
                <div class="auth-input-wrap">
                    @include('auth._icon', ['icon' => 'lock', 'class' => 'auth-input-icon'])
                    <input class="auth-input" id="password" type="password" name="password" placeholder="{{ __('Enter your password') }}" required autocomplete="current-password" @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                    <button class="auth-password-toggle" id="auth-password-toggle" type="button" aria-label="{{ __('Show password') }}" title="{{ __('Show password') }}" aria-controls="password" aria-pressed="false" data-show-label="{{ __('Show password') }}" data-hide-label="{{ __('Hide password') }}">
                        <span data-eye>@include('auth._icon', ['icon' => 'eye'])</span><span data-eye-off hidden>@include('auth._icon', ['icon' => 'eye-off'])</span>
                    </button>
                </div>
                @error('password')<span id="password-error" class="auth-field-error">{{ $message }}</span>@enderror
            </div>
            <div class="auth-options">
                <label class="auth-remember" for="remember"><input type="checkbox" name="remember" id="remember" value="1" @checked(old('remember'))><span>{{ __('Remember Me') }}</span></label>
                @if(Route::has('password.request'))<a href="{{ route('password.request') }}">{{ __('Forgot Your Password?') }}</a>@endif
            </div>
            <button class="auth-submit" type="submit"><span data-submit-label>{{ __('Sign in to dashboard') }}</span><span data-submit-arrow>@include('auth._icon', ['icon' => 'arrow'])</span><span class="auth-submit-spinner" data-submit-spinner hidden aria-hidden="true"></span></button>
        </form>
        <a class="auth-back" href="{{ route('fronted.index') }}">@include('auth._icon', ['icon' => 'arrow']){{ __('Back to website') }}</a>
    </div>
</section>
@endsection
