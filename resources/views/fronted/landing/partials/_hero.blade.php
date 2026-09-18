<header class="lk-hero">
    <div class="lk-container">
        <div class="lk-hero-grid">
            <div>
                <span class="lk-hero-badge"><i class="fa-solid fa-medal"></i> {{ $settings->hero_badge_text ?? __('Biladalrafidain Logistics · Shipping & Customs Clearance') }}</span>
                <h1>{{ $settings->hero_headline ?? __('Fast, Reliable Shipping Between Egypt and Iraq') }}</h1>
                <p class="lead">{{ $settings->hero_subheadline ?? '' }}</p>
                <div class="lk-hero-ctas">
                    <a href="tel:{{ $settings->phone_href ?? '' }}" class="lk-btn lk-btn-primary">
                        <i class="fa-solid fa-phone"></i> {{ $settings->hero_cta_label ?? __('Call Now') }}
                    </a>
                    <a href="{{ route('fronted.tracking') }}" class="lk-btn lk-btn-outline-light">
                        <i class="fa-solid fa-magnifying-glass-location"></i> {{ $settings->hero_secondary_cta_label ?? __('Track Your Shipment') }}
                    </a>
                </div>
            </div>
            <div class="lk-hero-media">
                @include('fronted.landing.partials._image-or-placeholder', [
                    'src' => $settings->hero_image ?? null,
                    'icon' => 'fa-ship',
                    'label' => __('Photo coming soon'),
                ])
            </div>
        </div>
    </div>
</header>
