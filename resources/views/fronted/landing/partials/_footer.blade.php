<footer class="lk-footer">
    <div class="lk-container">
        <div class="lk-footer-grid">
            <div>
                <h4>{{ $settings->company_name ?? 'بلاد الرافدين' }}</h4>
                <p style="max-width:320px;">{{ $settings->footer_description ?? __('Shipping and customs clearance services — Biladalrafidain Logistics.') }}</p>
                @if($settings->certifications_text ?? null)
                    <p style="font-size:.85rem;">{{ $settings->certifications_text }}</p>
                @endif
            </div>
            <div>
                <h4>{{ __('Contact') }}</h4>
                <ul>
                    @if($settings->company_address ?? null)
                        <li><i class="fa-solid fa-location-dot"></i> {{ $settings->company_address }}</li>
                    @endif
                    @if($settings->phone_display ?? null)
                        <li><a href="tel:{{ $settings->phone_href }}"><i class="fa-solid fa-phone"></i> {{ $settings->phone_display }}</a></li>
                    @endif
                    @if($settings->whatsapp_url ?? null)
                        <li><a href="{{ $settings->whatsapp_url }}" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a></li>
                    @endif
                    @if($settings->company_email ?? null)
                        <li><a href="mailto:{{ $settings->company_email }}"><i class="fa-solid fa-envelope"></i> {{ $settings->company_email }}</a></li>
                    @endif
                    @php
                        $socialLinks = [
                            'facebook_url' => 'fa-facebook-f',
                            'instagram_url' => 'fa-instagram',
                            'twitter_url' => 'fa-x-twitter',
                            'youtube_url' => 'fa-youtube',
                        ];
                        $socialLinks = collect($socialLinks)
                            ->map(fn ($icon, $field) => ['url' => $settings->{$field} ?? null, 'icon' => $icon])
                            ->filter(fn ($s) => filter_var($s['url'], FILTER_VALIDATE_URL));
                    @endphp
                    @foreach($socialLinks as $social)
                        @php
                            $display = preg_replace('#^https?://(www\.)?#i', '', $social['url']);
                            $display = rtrim($display, '/');
                        @endphp
                        <li><a href="{{ $social['url'] }}" target="_blank" rel="noopener"><i class="fa-brands {{ $social['icon'] }}"></i> {{ $display }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h4>{{ __('Policies') }}</h4>
                <ul>
                    @forelse ($footerPolicies ?? [] as $policy)
                        <li><a href="{{ route('fronted.policies') }}#{{ $policy->slug }}">{{ $policy->title }}</a></li>
                    @empty
                        <li><a href="{{ route('fronted.policies') }}">{{ __('Policies') }}</a></li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="lk-footer-bottom">
            <span>&copy; {{ date('Y') }} {{ $settings->company_name ?? 'بلاد الرافدين' }}</span>
            <span>{{ __('All Rights Reserved') }}</span>
        </div>
    </div>
</footer>
