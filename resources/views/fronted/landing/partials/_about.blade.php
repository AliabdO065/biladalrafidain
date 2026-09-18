<section class="lk-section" id="lk-about">
    <div class="lk-container">
        <div class="lk-about-grid">
            <div class="lk-about-photo">
                @include('fronted.landing.partials._image-or-placeholder', [
                    'src' => $settings->about_owner_photo ?? null,
                    'icon' => 'fa-user',
                    'label' => $settings->about_owner_name ?? __('Founder & CEO'),
                ])
                <div class="lk-about-name">{{ $settings->about_owner_name ?? '' }}</div>
            </div>
            <div>
                <span class="lk-eyebrow">{{ $settings->about_eyebrow ?? __('About Us') }}</span>
                <h2 class="lk-h2">{{ $settings->about_heading ?? __('A Logistics Partner Trusted Across the Region') }}</h2>
                <p style="color:var(--lk-gray-600);font-size:1.02rem;">{{ $settings->about_story ?? '' }}</p>

                <div class="lk-trust-list">
                    @for($i = 1; $i <= 4; $i++)
                        @php
                            $title = $settings->{"trust_{$i}_title"} ?? null;
                            $text = $settings->{"trust_{$i}_text"} ?? null;
                        @endphp
                        @if($title)
                            <div class="lk-trust-item">
                                <i class="fa-solid fa-circle-check"></i>
                                <div>
                                    <h4>{{ $title }}</h4>
                                    <p>{{ $text }}</p>
                                </div>
                            </div>
                        @endif
                    @endfor
                </div>
            </div>
        </div>
    </div>
</section>
