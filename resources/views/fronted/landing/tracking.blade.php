@extends('fronted.layouts.landing')

@section('content')
    @include('fronted.landing.partials._navbar')

    <header class="lk-hero" style="padding-bottom:40px;">
        <div class="lk-container">
            <div style="max-width:640px;margin:0 auto;text-align:center;">
                <span class="lk-hero-badge"><i class="fa-solid fa-magnifying-glass-location"></i> {{ __('Track Shipment') }}</span>
                <h1>{{ __('Where Is Your Shipment?') }}</h1>
                <p class="lead">{{ __('Enter your tracking code below to see the latest status.') }}</p>

                <form method="GET" action="{{ route('fronted.tracking') }}" style="display:flex;gap:10px;margin-top:20px;">
                    <input type="text" name="code" value="{{ $code ?? '' }}" class="lk-form-control" placeholder="{{ __('Enter tracking code') }}" required style="flex:1;">
                    <button type="submit" class="lk-btn lk-btn-primary">
                        <i class="fa-solid fa-magnifying-glass"></i> {{ __('Track') }}
                    </button>
                </form>
            </div>
        </div>
    </header>

    <section class="lk-section">
        <div class="lk-container" style="max-width:760px;">
            @if($notFound)
                <div class="lk-thanks">
                    <i class="fa-solid fa-circle-exclamation" style="color:#dc3545;"></i>
                    <h3>{{ __('Shipment Not Found') }}</h3>
                    <p>{{ __('We could not find a shipment with this tracking code. Please check the code or contact us for help.') }}</p>
                    <a href="tel:{{ $settings->phone_href ?? '' }}" class="lk-btn lk-btn-primary" style="margin-top:12px;">
                        <i class="fa-solid fa-phone"></i> {{ __('Call Now') }}
                    </a>
                </div>
            @elseif($shipment)
                <div class="lk-card" style="padding:24px;">
                    <div class="lk-section-head" style="text-align:start;margin-bottom:20px;">
                        <span class="lk-eyebrow">{{ __('Tracking Code') }}: {{ $shipment->tracking_code }}</span>
                        <h2 class="lk-h2" style="font-size:1.4rem;">{{ $shipment->origin }} <i class="fa-solid fa-arrow-right-long" style="margin:0 8px;"></i> {{ $shipment->destination }}</h2>
                    </div>

                    <div class="lk-trust-list" style="margin-bottom:20px;">
                        @if($shipment->sender_name)
                            <div class="lk-trust-item">
                                <i class="fa-solid fa-user"></i>
                                <div><h4>{{ __('Sender') }}</h4><p>{{ $shipment->sender_name }}</p></div>
                            </div>
                        @endif
                        @if($shipment->receiver_name)
                            <div class="lk-trust-item">
                                <i class="fa-solid fa-user-check"></i>
                                <div><h4>{{ __('Receiver') }}</h4><p>{{ $shipment->receiver_name }}</p></div>
                            </div>
                        @endif
                        @if($shipment->estimated_delivery)
                            <div class="lk-trust-item">
                                <i class="fa-solid fa-calendar-check"></i>
                                <div><h4>{{ __('Estimated Delivery') }}</h4><p>{{ $shipment->estimated_delivery->format('d.m.Y') }}</p></div>
                            </div>
                        @endif
                    </div>

                    <h3 style="margin-bottom:14px;">{{ __('Tracking History') }}</h3>
                    <div class="lk-faq-list">
                        @forelse($shipment->events as $event)
                            <div class="lk-faq-item" style="cursor:default;">
                                <div class="lk-faq-q" style="cursor:default;">
                                    <span><i class="fa-solid fa-circle-check" style="color:#198754;margin-inline-end:8px;"></i> {{ $event->status }}</span>
                                    <span style="font-size:.85rem;color:var(--lk-gray-600,#777);">{{ $event->happened_at->format('d.m.Y H:i') }}</span>
                                </div>
                                @if($event->note)
                                    <div class="lk-faq-a" style="display:block;">{{ $event->note }}</div>
                                @endif
                            </div>
                        @empty
                            <p>{{ __('No tracking updates yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>
    </section>

    @include('fronted.landing.partials._footer')
    @include('fronted.landing.partials._sticky-bar')
@endsection
