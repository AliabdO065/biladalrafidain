<section class="lk-section" id="lk-reviews">
    <div class="lk-container">
        <div class="lk-section-head">
            <span class="lk-eyebrow">{{ $settings->reviews_eyebrow ?? __('Client Testimonials') }}</span>
            <h2 class="lk-h2">{{ $settings->reviews_heading ?? __('What Our Clients Say') }}</h2>
        </div>

        <div class="lk-reviews-grid">
            @foreach($reviews as $review)
                <div class="lk-review-card">
                    <div class="lk-review-head">
                        <div class="lk-review-avatar">
                            @include('fronted.landing.partials._image-or-placeholder', [
                                'src' => $review->author_photo,
                                'icon' => 'fa-user',
                                'label' => '',
                                'class' => 'lk-review-avatar',
                            ])
                        </div>
                        <div>
                            <div class="lk-review-name">{{ $review->author_name }}</div>
                            <div class="lk-review-date">{{ optional($review->review_date)->format('d.m.Y') }}</div>
                        </div>
                    </div>
                    <div class="lk-stars">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fa-{{ $i <= $review->rating ? 'solid' : 'regular' }} fa-star"></i>
                        @endfor
                    </div>
                    <p class="lk-review-text">{{ $review->review_text }}</p>
                    @if($review->is_placeholder)
                        <span class="lk-placeholder-badge">{{ __('Sample Review') }}</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
