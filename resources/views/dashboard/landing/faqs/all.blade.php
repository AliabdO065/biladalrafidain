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
                    <span><i class="fa-solid fa-heading"></i> {{ __('Abschnitts-Überschrift') }}</span>
                    <i class="fa-solid fa-chevron-down lk-collapse-caret"></i>
                </button>
                <div class="collapse" id="heading-form-body">
                    <div class="card-body">
                        <form action="{{ route('dashboard.landing.settings.update') }}" method="POST">
                            @csrf
                            @include('dashboard.landing.partials._translatable-field', ['name'=>'faq_eyebrow', 'label'=>__('Kicker'), 'values'=>$settings->translationsFor('faq_eyebrow'), 'required'=>false])
                            @include('dashboard.landing.partials._translatable-field', ['name'=>'faq_heading', 'label'=>__('Überschrift'), 'values'=>$settings->translationsFor('faq_heading'), 'required'=>false])
                            <button type="submit" class="btn btn-primary">{{ __('Speichern') }}</button>
                        </form>
                    </div>
                </div>
            </div>

            <a href="{{ route('dashboard.landing.faqs.add') }}" class="btn btn-success">{{ __('Frage hinzufügen') }}</a>
            <hr>
            <div style="display:flex;flex-wrap:wrap;gap:20px;">
                @foreach ($items as $item)
                    <div class="card" style="width: calc(33% - 20px);">
                        <div class="card-body">
                            <h5 class="card-title">{{ $item->question }}</h5>
                            <p class="card-text">{{ $item->answer }}</p>
                            <a href="{{ route('dashboard.landing.faqs.edit', $item->id) }}" class="btn btn-primary btn-sm">{{ __('Bearbeiten') }}</a>
                            <a href="{{ route('dashboard.landing.faqs.delete', $item->id) }}" class="btn btn-danger btn-sm delete-confirm">{{ __('Löschen') }}</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
