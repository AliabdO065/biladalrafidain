<section class="lk-section" id="lk-callback">
    <div class="lk-container">
        <div class="lk-section-head">
            <span class="lk-eyebrow">{{ $settings->callback_eyebrow ?? __('Have a Question?') }}</span>
            <h2 class="lk-h2">{{ $settings->callback_heading ?? __('Request a Callback') }}</h2>
            <p>{{ $settings->callback_subtext ?? __('Tell us briefly what you need — we will call you back shortly.') }}</p>
        </div>

        <div class="lk-callback-box">
            @if(session('callback_success'))
                <div class="lk-thanks">
                    <i class="fa-solid fa-circle-check"></i>
                    <h3>{{ __('Thank You!') }}</h3>
                    <p>{{ __('We received your request and will contact you shortly.') }}</p>
                </div>
            @else
                <form id="lk-callback-form" method="POST" action="{{ route('fronted.landing.callback') }}"
                      data-sending-label="{{ __('Sending...') }}"
                      data-submit-label="{{ __('Request a Callback') }}"
                      data-error-message="{{ __('Something went wrong. Please contact us directly.') }}">
                    @csrf
                    <input type="hidden" name="problem_type" id="lk-problem-type">

                    <div class="lk-callback-steps">
                        <span class="lk-callback-dot active" data-step="1"></span>
                        <span class="lk-callback-dot" data-step="2"></span>
                        <span class="lk-callback-dot" data-step="3"></span>
                    </div>

                    <div class="lk-callback-panel active" data-step="1">
                        <h3 style="text-align:center;margin:0 0 6px;">{{ __('What do you need?') }}</h3>
                        <div class="lk-option-grid">
                            <button type="button" class="lk-option-btn" data-problem="shipping"><i class="fa-solid fa-truck-fast"></i> {{ __('Shipping') }}</button>
                            <button type="button" class="lk-option-btn" data-problem="customs_clearance"><i class="fa-solid fa-passport"></i> {{ __('Customs Clearance') }}</button>
                            <button type="button" class="lk-option-btn" data-problem="tracking_issue"><i class="fa-solid fa-magnifying-glass-location"></i> {{ __('Tracking Issue') }}</button>
                            <button type="button" class="lk-option-btn" data-problem="other"><i class="fa-solid fa-circle-question"></i> {{ __('Other') }}</button>
                        </div>
                    </div>

                    <div class="lk-callback-panel" data-step="2">
                        <h3 style="text-align:center;margin:0 0 6px;">{{ __('Where is your shipment going?') }}</h3>
                        <input type="text" name="postal_code" id="lk-postal-code" class="lk-form-control" placeholder="{{ __('Destination city or country') }}" required>
                        <div class="lk-callback-nav">
                            <button type="button" class="lk-btn lk-btn-outline-light" data-action="back">{{ __('Back') }}</button>
                            <button type="button" class="lk-btn lk-btn-primary" data-action="next-2">{{ __('Next') }}</button>
                        </div>
                    </div>

                    <div class="lk-callback-panel" data-step="3">
                        <h3 style="text-align:center;margin:0 0 6px;">{{ __('Your Contact Details') }}</h3>
                        <div class="lk-form-row">
                            <input type="text" name="name" class="lk-form-control" placeholder="{{ __('Your Name') }}" required>
                            <input type="tel" name="phone" class="lk-form-control" placeholder="{{ __('Phone Number') }}" required>
                        </div>
                        <input type="email" name="email" class="lk-form-control" placeholder="{{ __('Email (optional)') }}">
                        <div class="lk-callback-nav">
                            <button type="button" class="lk-btn lk-btn-outline-light" data-action="back">{{ __('Back') }}</button>
                            <button type="submit" class="lk-btn lk-btn-primary" data-action="submit">{{ __('Request a Callback') }}</button>
                        </div>
                    </div>

                    <div class="lk-callback-panel" data-step="4">
                        <div class="lk-thanks">
                            <i class="fa-solid fa-circle-check"></i>
                            <h3>{{ __('Thank You!') }}</h3>
                            <p>{{ __('We received your request and will contact you shortly.') }}</p>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>
</section>
