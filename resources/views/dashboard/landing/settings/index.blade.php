@extends('dashboard.layouts.layout')
@section('content')
<div class="wrapper">
    <div class="page">
        <div class="page-inner">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('dashboard.landing.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="card mb-4" id="section-navbar">
                    <button class="card-header lk-collapse-toggle collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-navbar" aria-expanded="false" aria-controls="collapse-navbar">
                        <span><i class="fa-solid fa-bars"></i> {{ __('Navbar') }}</span>
                        <i class="fa-solid fa-chevron-down lk-collapse-caret"></i>
                    </button>
                    <div class="collapse" id="collapse-navbar">
                        <div class="card-body">
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="navbar_show_brand_text" name="navbar_show_brand_text" value="1" {{ $settings->navbar_show_brand_text ? 'checked' : '' }}>
                                <label class="form-check-label" for="navbar_show_brand_text">{{ __('Name neben dem Logo anzeigen') }}</label>
                            </div>
                            @include('dashboard.landing.partials._translatable-field', ['name'=>'navbar_brand_text', 'label'=>__('Name in der Navigationsleiste'), 'values'=>$settings->translationsFor('navbar_brand_text'), 'required'=>false])

                            <h6 class="lk-settings-subhead">{{ __('Menüpunkte') }}</h6>
                            <div class="mb-2 form-check">
                                <input type="checkbox" class="form-check-input" id="nav_show_services" name="nav_show_services" value="1" {{ $settings->nav_show_services ? 'checked' : '' }}>
                                <label class="form-check-label" for="nav_show_services">{{ __('Leistungen') }}</label>
                            </div>
                            <div class="mb-2 form-check">
                                <input type="checkbox" class="form-check-input" id="nav_show_steps" name="nav_show_steps" value="1" {{ $settings->nav_show_steps ? 'checked' : '' }}>
                                <label class="form-check-label" for="nav_show_steps">{{ __('Ablauf') }}</label>
                            </div>
                            <div class="mb-2 form-check">
                                <input type="checkbox" class="form-check-input" id="nav_show_about" name="nav_show_about" value="1" {{ $settings->nav_show_about ? 'checked' : '' }}>
                                <label class="form-check-label" for="nav_show_about">{{ __('Über uns') }}</label>
                            </div>
                            <div class="mb-2 form-check">
                                <input type="checkbox" class="form-check-input" id="nav_show_comparison" name="nav_show_comparison" value="1" {{ $settings->nav_show_comparison ? 'checked' : '' }}>
                                <label class="form-check-label" for="nav_show_comparison">{{ __('Vergleich') }}</label>
                            </div>
                            <div class="mb-2 form-check">
                                <input type="checkbox" class="form-check-input" id="nav_show_reviews" name="nav_show_reviews" value="1" {{ $settings->nav_show_reviews ? 'checked' : '' }}>
                                <label class="form-check-label" for="nav_show_reviews">{{ __('Bewertungen') }}</label>
                            </div>
                            <div class="mb-2 form-check">
                                <input type="checkbox" class="form-check-input" id="nav_show_faq" name="nav_show_faq" value="1" {{ $settings->nav_show_faq ? 'checked' : '' }}>
                                <label class="form-check-label" for="nav_show_faq">{{ __('FAQ') }}</label>
                            </div>
                            <div class="mb-2 form-check">
                                <input type="checkbox" class="form-check-input" id="nav_show_callback" name="nav_show_callback" value="1" {{ $settings->nav_show_callback ? 'checked' : '' }}>
                                <label class="form-check-label" for="nav_show_callback">{{ __('Kontakt') }}</label>
                            </div>
                            <div class="mb-2 form-check">
                                <input type="checkbox" class="form-check-input" id="nav_show_tracking" name="nav_show_tracking" value="1" {{ $settings->nav_show_tracking ? 'checked' : '' }}>
                                <label class="form-check-label" for="nav_show_tracking">{{ __('Track Shipment') }}</label>
                            </div>
                            <div class="mb-2 form-check">
                                <input type="checkbox" class="form-check-input" id="nav_show_policies" name="nav_show_policies" value="1" {{ $settings->nav_show_policies ? 'checked' : '' }}>
                                <label class="form-check-label" for="nav_show_policies">{{ __('Policies') }}</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4" id="section-banner">
                    <button class="card-header lk-collapse-toggle collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-banner" aria-expanded="false" aria-controls="collapse-banner">
                        <span><i class="fa-solid fa-triangle-exclamation"></i> {{ __('Warnbanner') }}</span>
                        <i class="fa-solid fa-chevron-down lk-collapse-caret"></i>
                    </button>
                    <div class="collapse" id="collapse-banner">
                        <div class="card-body">
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="alert_banner_active" name="alert_banner_active" value="1" {{ $settings->alert_banner_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="alert_banner_active">{{ __('Banner anzeigen') }}</label>
                            </div>
                            @include('dashboard.landing.partials._translatable-field', ['name'=>'alert_banner_text', 'label'=>__('Bannertext'), 'values'=>$settings->translationsFor('alert_banner_text'), 'required'=>false])
                        </div>
                    </div>
                </div>

                <div class="card mb-4" id="section-logo">
                    <button class="card-header lk-collapse-toggle collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-logo" aria-expanded="false" aria-controls="collapse-logo">
                        <span><i class="fa-solid fa-phone"></i> {{ __('Logo & Telefon') }}</span>
                        <i class="fa-solid fa-chevron-down lk-collapse-caret"></i>
                    </button>
                    <div class="collapse" id="collapse-logo">
                        <div class="card-body">
                            <div class="mb-3 form-inline">
                                <label>{{ __('Logo') }}</label>
                                <input type="file" class="form-control" name="logo_image_file">
                                @if($settings->logo_image)
                                    <img src="{{ asset($settings->logo_image) }}" style="width:80px;margin-left:20px;" alt="Logo">
                                @endif
                            </div>
                            <div class="mb-3">
                                <label>{{ __('Telefonnummer (Anzeige)') }}</label>
                                <input type="text" class="form-control" name="phone_display" value="{{ $settings->phone_display }}" required>
                            </div>
                            <div class="mb-3">
                                <label>{{ __('Telefonnummer (tel:-Link, z.B. +492211234567)') }}</label>
                                <input type="text" class="form-control" name="phone_href" value="{{ $settings->phone_href }}" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4" id="section-rating">
                    <button class="card-header lk-collapse-toggle collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-rating" aria-expanded="false" aria-controls="collapse-rating">
                        <span><i class="fa-solid fa-star"></i> {{ __('Bewertung (Sticky-Bar)') }}</span>
                        <i class="fa-solid fa-chevron-down lk-collapse-caret"></i>
                    </button>
                    <div class="collapse" id="collapse-rating">
                        <div class="card-body">
                            <div class="mb-3 form-inline">
                                <label>{{ __('Wert') }}</label>
                                <input type="number" step="0.1" min="0" max="5" style="width:120px" class="form-control" name="rating_value" value="{{ $settings->rating_value }}">
                                <label>{{ __('Anzahl Bewertungen') }}</label>
                                <input type="number" style="width:160px" class="form-control" name="rating_count" value="{{ $settings->rating_count }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4" id="section-footer">
                    <button class="card-header lk-collapse-toggle collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-footer" aria-expanded="false" aria-controls="collapse-footer">
                        <span><i class="fa-solid fa-building"></i> {{ __('Footer') }}</span>
                        <i class="fa-solid fa-chevron-down lk-collapse-caret"></i>
                    </button>
                    <div class="collapse" id="collapse-footer">
                        <div class="card-body">
                            @include('dashboard.landing.partials._translatable-field', ['name'=>'company_name', 'label'=>__('Firmenname'), 'values'=>$settings->translationsFor('company_name')])
                            @include('dashboard.landing.partials._translatable-field', ['name'=>'footer_description', 'label'=>__('Kurzbeschreibung'), 'type'=>'textarea', 'rows'=>2, 'values'=>$settings->translationsFor('footer_description'), 'required'=>false])
                            @include('dashboard.landing.partials._translatable-field', ['name'=>'certifications_text', 'label'=>__('Zertifizierungen / Mitgliedschaften'), 'values'=>$settings->translationsFor('certifications_text'), 'required'=>false])

                            <h6 class="lk-settings-subhead">{{ __('Kontakt') }}</h6>
                            <div class="mb-3">
                                <label>{{ __('Adresse') }}</label>
                                <input type="text" class="form-control" name="company_address" value="{{ $settings->company_address }}">
                            </div>
                            <div class="mb-3">
                                <label>{{ __('E-Mail') }}</label>
                                <input type="email" class="form-control" name="company_email" value="{{ $settings->company_email }}">
                            </div>
                            <p class="text-muted">{{ __('Nur ausgefüllte Links werden im Footer der Website angezeigt.') }}</p>
                            <div class="mb-3">
                                <label>Facebook</label>
                                <input type="text" class="form-control" placeholder="https://facebook.com/..." name="facebook_url" value="{{ $settings->facebook_url }}">
                            </div>
                            <div class="mb-3">
                                <label>Instagram</label>
                                <input type="text" class="form-control" placeholder="https://instagram.com/..." name="instagram_url" value="{{ $settings->instagram_url }}">
                            </div>
                            <div class="mb-3">
                                <label>Twitter / X</label>
                                <input type="text" class="form-control" placeholder="https://x.com/..." name="twitter_url" value="{{ $settings->twitter_url }}">
                            </div>
                            <div class="mb-3">
                                <label>YouTube</label>
                                <input type="text" class="form-control" placeholder="https://youtube.com/..." name="youtube_url" value="{{ $settings->youtube_url }}">
                            </div>
                            <div class="mb-3">
                                <label>WhatsApp</label>
                                <input type="text" class="form-control" placeholder="https://wa.me/201011961595" name="whatsapp_url" value="{{ $settings->whatsapp_url }}">
                            </div>

                            <h6 class="lk-settings-subhead">{{ __('Rechtliche Links') }}</h6>
                            <div class="mb-3">
                                <label>{{ __('Impressum-URL') }}</label>
                                <input type="text" class="form-control" placeholder="https://..." name="impressum_url" value="{{ $settings->impressum_url }}">
                            </div>
                            <div class="mb-3">
                                <label>{{ __('Datenschutz-URL') }}</label>
                                <input type="text" class="form-control" placeholder="https://..." name="privacy_url" value="{{ $settings->privacy_url }}">
                            </div>
                            <div class="mb-3">
                                <label>{{ __('AGB-URL') }}</label>
                                <input type="text" class="form-control" placeholder="https://..." name="terms_url" value="{{ $settings->terms_url }}">
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary lk-settings-save">{{ __('Speichern') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
