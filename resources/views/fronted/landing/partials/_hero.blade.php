<header class="lk-hero lk-hero-logistics" @if($settings->hero_image ?? null) style="--hero-photo:url('{{ asset($settings->hero_image) }}')" @endif>
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
            <div class="lk-hero-route" aria-hidden="true"><span>EGYPT</span><i class="fa-solid fa-truck-fast"></i><span>IRAQ</span></div>
        </div>
    </div>
</header>
<section class="lk-quick-track"><div class="lk-container"><div><span class="lk-section-eyebrow">{{ __('trade.front.track_eyebrow') }}</span><h2>{{ __('Track Your Shipment') }}</h2></div><form action="{{ route('fronted.tracking') }}" method="GET"><label class="lk-sr-only" for="home-tracking">{{ __('Tracking Code') }}</label><input id="home-tracking" name="code" dir="ltr" maxlength="191" placeholder="BR-…" required><button class="lk-btn lk-btn-primary">{{ __('Track Shipment') }} <i class="fa-solid fa-arrow-right"></i></button></form></div></section>
<section class="lk-route-services"><div class="lk-container">@foreach(['truck-fast'=>'road','box-open'=>'cargo','file-shield'=>'customs','location-dot'=>'tracking'] as $icon=>$key)<div><span class="lk-route-service-icon"><i class="fa-solid fa-{{ $icon }}"></i></span><div><h2>{{ __('trade.front.'.$key) }}</h2><p>{{ __('trade.front.'.$key.'_text') }}</p></div></div>@endforeach</div></section>
