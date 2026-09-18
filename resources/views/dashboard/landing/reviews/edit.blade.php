@extends('dashboard.layouts.layout')
@section('content')
<div class="wrapper">
    <div class="page">
        <div class="page-inner">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            <form action="{{ route('dashboard.landing.reviews.update', $item->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label>{{ __('Name') }}</label>
                    <input type="text" class="form-control" name="author_name" value="{{ $item->author_name }}" required>
                </div>
                <div class="mb-3 form-inline">
                    <label>{{ __('Foto') }}</label>
                    <input type="file" class="form-control" name="author_photo">
                    @if($item->author_photo)
                        <img src="{{ asset($item->author_photo) }}" style="width:60px;border-radius:50%;margin-left:20px;" alt="{{ $item->author_name }}">
                    @endif
                </div>
                <div class="mb-3">
                    <label>{{ __('Sterne (1-5)') }}</label>
                    <input type="number" class="form-control" name="rating" min="1" max="5" value="{{ $item->rating }}" required>
                </div>
                <div class="mb-3">
                    <label>{{ __('Datum') }}</label>
                    <input type="date" class="form-control" name="review_date" value="{{ optional($item->review_date)->format('Y-m-d') }}">
                </div>
                @include('dashboard.landing.partials._translatable-field', ['name'=>'review_text', 'label'=>__('Bewertungstext'), 'type'=>'textarea', 'rows'=>4, 'values'=>$item->translationsFor('review_text')])
                <div class="mb-3">
                    <label>{{ __('Reihenfolge') }}</label>
                    <input type="number" class="form-control" name="sort_order" value="{{ $item->sort_order }}">
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_placeholder" name="is_placeholder" value="1" {{ $item->is_placeholder ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_placeholder">{{ __('Als Beispiel kennzeichnen (noch keine echte Bewertung)') }}</label>
                </div>
                <button type="submit" class="btn btn-primary">{{ __('Aktualisieren') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
