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
                            @include('dashboard.landing.partials._translatable-field', ['name'=>'reviews_eyebrow', 'label'=>__('Kicker'), 'values'=>$settings->translationsFor('reviews_eyebrow'), 'required'=>false])
                            @include('dashboard.landing.partials._translatable-field', ['name'=>'reviews_heading', 'label'=>__('Überschrift'), 'values'=>$settings->translationsFor('reviews_heading'), 'required'=>false])
                            <button type="submit" class="btn btn-primary">{{ __('Speichern') }}</button>
                        </form>
                    </div>
                </div>
            </div>

            <a href="{{ route('dashboard.landing.reviews.add') }}" class="btn btn-success">{{ __('Bewertung hinzufügen') }}</a>
            <hr>
            <div style="display:flex;flex-wrap:wrap;gap:20px;">
                @foreach ($items as $item)
                    <div class="card" style="width: calc(33% - 20px);">
                        @if($item->author_photo)
                            <img src="{{ asset($item->author_photo) }}" class="card-img-top" style="height:180px;object-fit:cover;" alt="{{ $item->author_name }}">
                        @endif
                        <div class="card-body">
                            <h5 class="card-title">{{ $item->author_name }} — {{ $item->rating }}★</h5>
                            <p class="card-text">{{ \Illuminate\Support\Str::limit($item->review_text, 100) }}</p>
                            @if($item->is_placeholder)
                                <span class="badge badge-sample">{{ __('Beispiel') }}</span>
                            @endif
                            <div class="mt-2">
                                <a href="{{ route('dashboard.landing.reviews.edit', $item->id) }}" class="btn btn-primary btn-sm">{{ __('Bearbeiten') }}</a>
                                <a href="{{ route('dashboard.landing.reviews.delete', $item->id) }}" class="btn btn-danger btn-sm delete-confirm">{{ __('Löschen') }}</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
