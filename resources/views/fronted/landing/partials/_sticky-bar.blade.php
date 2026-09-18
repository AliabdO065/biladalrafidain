<div class="lk-sticky-bar">
    <div class="lk-container lk-sticky-inner">
        <span class="lk-sticky-rating">
            <i class="fa-solid fa-star"></i>
            {{ number_format($settings->rating_value ?? 4.9, 1, ',', '.') }} · {{ __(':count+ Google Reviews', ['count' => $settings->rating_count ?? 0]) }}
        </span>
        <a href="tel:{{ $settings->phone_href ?? '' }}" class="lk-btn lk-btn-outline lk-btn-sm">
            <i class="fa-solid fa-phone"></i> {{ $settings->phone_display ?? '' }}
        </a>
    </div>
</div>
