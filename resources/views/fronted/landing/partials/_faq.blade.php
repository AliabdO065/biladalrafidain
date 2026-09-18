<section class="lk-section lk-section-alt" id="lk-faq">
    <div class="lk-container" style="max-width:760px;">
        <div class="lk-section-head">
            <span class="lk-eyebrow">{{ $settings->faq_eyebrow ?? __('FAQ') }}</span>
            <h2 class="lk-h2">{{ $settings->faq_heading ?? __('Good to Know') }}</h2>
        </div>

        <div class="lk-faq-list">
            @foreach($faqs as $faq)
                <div class="lk-faq-item">
                    <button type="button" class="lk-faq-q">
                        {{ $faq->question }}
                        <i class="fa-solid fa-plus"></i>
                    </button>
                    <div class="lk-faq-a">{{ $faq->answer }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>
