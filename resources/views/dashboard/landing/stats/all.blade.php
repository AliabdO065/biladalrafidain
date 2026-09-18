@extends('dashboard.layouts.layout')
@section('content')
<div class="wrapper">
    <div class="page">
        <div class="page-inner">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card mb-4">
                <button class="card-header lk-collapse-toggle collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#heading-form-body" aria-expanded="false" aria-controls="heading-form-body">
                    <span><i class="fa-solid fa-house"></i> {{ __('Hero-Bereich') }}</span>
                    <i class="fa-solid fa-chevron-down lk-collapse-caret"></i>
                </button>
                <div class="collapse" id="heading-form-body">
                    <div class="card-body">
                        <form action="{{ route('dashboard.landing.settings.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @include('dashboard.landing.partials._translatable-field', ['name'=>'hero_badge_text', 'label'=>__('Badge-Text (über der Überschrift)'), 'values'=>$settings->translationsFor('hero_badge_text'), 'required'=>false])
                            @include('dashboard.landing.partials._translatable-field', ['name'=>'hero_headline', 'label'=>__('Überschrift'), 'values'=>$settings->translationsFor('hero_headline')])
                            @include('dashboard.landing.partials._translatable-field', ['name'=>'hero_subheadline', 'label'=>__('Unterüberschrift'), 'type'=>'textarea', 'rows'=>2, 'values'=>$settings->translationsFor('hero_subheadline'), 'required'=>false])
                            @include('dashboard.landing.partials._translatable-field', ['name'=>'hero_cta_label', 'label'=>__('Button-Text'), 'values'=>$settings->translationsFor('hero_cta_label'), 'required'=>false])
                            @include('dashboard.landing.partials._translatable-field', ['name'=>'hero_secondary_cta_label', 'label'=>__('Button-Text (zweiter Button)'), 'values'=>$settings->translationsFor('hero_secondary_cta_label'), 'required'=>false])
                            <div class="mb-3 form-inline">
                                <label>{{ __('Hero-Bild') }}</label>
                                <input type="file" class="form-control" name="hero_image_file">
                                @if($settings->hero_image)
                                    <img src="{{ asset($settings->hero_image) }}" style="width:100px;margin-left:20px;" alt="Hero">
                                @endif
                            </div>
                            <button type="submit" class="btn btn-primary">{{ __('Speichern') }}</button>
                        </form>
                    </div>
                </div>
            </div>

            <a href="{{ route('dashboard.landing.stats.add') }}" class="btn btn-success">{{ __('Eintrag hinzufügen') }}</a>
            <hr>
            <div style="display:flex;flex-wrap:wrap;gap:20px;">
                @foreach ($items as $item)
                    <div class="card" style="width: 260px;">
                        <div class="card-body">
                            <h5 class="card-title">{{ $item->value }}</h5>
                            <p class="card-text">{{ $item->label }}</p>
                            <p class="card-text"><small class="text-muted">{{ __('Icon') }}: {{ $item->icon }} · {{ __('Reihenfolge') }}: {{ $item->sort_order }}</small></p>
                            <a href="{{ route('dashboard.landing.stats.edit', $item->id) }}" class="btn btn-primary">{{ __('Bearbeiten') }}</a>
                            <a href="{{ route('dashboard.landing.stats.delete', $item->id) }}" class="btn btn-danger delete-confirm">{{ __('Löschen') }}</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
