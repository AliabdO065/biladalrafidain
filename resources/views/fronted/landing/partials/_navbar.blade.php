<nav class="lk-navbar">
    <div class="lk-container lk-navbar-inner">
        <a href="{{ route('fronted.index') }}" class="lk-logo">
            @if($settings && $settings->logo_image)
                <img src="{{ asset($settings->logo_image) }}" alt="{{ $settings->company_name }}" style="height:36px;width:36px;border-radius:8px;object-fit:cover;">
            @else
                <span class="lk-logo-mark"><i class="fa-solid fa-ship"></i></span>
            @endif
            @if($settings->navbar_show_brand_text ?? true)
                {{ $settings->navbar_brand_text ?? $settings->company_name ?? 'بلاد الرافدين' }}
            @endif
        </a>

        <ul class="lk-nav-links">
            @if($settings->nav_show_services ?? true)
                <li><a href="{{ route('fronted.index') }}#lk-services">{{ __('Services') }}</a></li>
            @endif
            @if($settings->nav_show_steps ?? true)
                <li><a href="{{ route('fronted.index') }}#lk-steps">{{ __('How It Works') }}</a></li>
            @endif
            @if($settings->nav_show_tracking ?? true)
                <li><a href="{{ route('fronted.tracking') }}">{{ __('Track Shipment') }}</a></li>
            @endif
            @if($settings->nav_show_about ?? true)
                <li><a href="{{ route('fronted.index') }}#lk-about">{{ __('About Us') }}</a></li>
            @endif
            @if($settings->nav_show_comparison ?? true)
                <li><a href="{{ route('fronted.index') }}#lk-comparison">{{ __('Why Choose Us') }}</a></li>
            @endif
            @if($settings->nav_show_reviews ?? true)
                <li><a href="{{ route('fronted.index') }}#lk-reviews">{{ __('Reviews') }}</a></li>
            @endif
            @if($settings->nav_show_faq ?? true)
                <li><a href="{{ route('fronted.index') }}#lk-faq">{{ __('FAQ') }}</a></li>
            @endif
            @if($settings->nav_show_policies ?? true)
                <li><a href="{{ route('fronted.policies') }}">{{ __('Policies') }}</a></li>
            @endif
            @if($settings->nav_show_callback ?? true)
                <li><a href="{{ route('fronted.index') }}#lk-callback">{{ __('Contact') }}</a></li>
            @endif
        </ul>

        <div class="lk-navbar-actions">
            @if(isset($enabledLanguages) && $enabledLanguages->count() > 1)
                <div class="lk-lang-switch">
                    <button type="button" class="lk-lang-current">
                        {{ strtoupper($currentLocale ?? app()->getLocale()) }} <i class="fa-solid fa-chevron-down"></i>
                    </button>
                    <ul class="lk-lang-menu">
                        @foreach($enabledLanguages as $lang)
                            <li>
                                <a href="{{ route('fronted.setLocale', $lang->code) }}"
                                   class="@if(($currentLocale ?? app()->getLocale()) === $lang->code) active @endif">
                                    {{ $lang->native_name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if($settings->whatsapp_url ?? null)
                <a href="{{ $settings->whatsapp_url }}" target="_blank" rel="noopener" class="lk-navbar-phone" style="margin-inline-end:8px;">
                    <i class="fa-brands fa-whatsapp"></i>
                </a>
            @endif
            <a href="tel:{{ $settings->phone_href ?? '' }}" class="lk-navbar-phone">
                <i class="fa-solid fa-phone"></i>{{ $settings->phone_display ?? '' }}
            </a>
        </div>
    </div>
</nav>
