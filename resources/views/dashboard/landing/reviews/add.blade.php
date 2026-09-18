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
            <form action="{{ route('dashboard.landing.reviews.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label>{{ __('Name') }}</label>
                    <input type="text" class="form-control" name="author_name" required>
                </div>
                <div class="mb-3">
                    <label>{{ __('Foto') }}</label>
                    <input type="file" class="form-control" name="author_photo">
                </div>
                <div class="mb-3">
                    <label>{{ __('Sterne (1-5)') }}</label>
                    <input type="number" class="form-control" name="rating" min="1" max="5" value="5" required>
                </div>
                <div class="mb-3">
                    <label>{{ __('Datum') }}</label>
                    <input type="date" class="form-control" name="review_date">
                </div>
                @include('dashboard.landing.partials._translatable-field', ['name'=>'review_text', 'label'=>__('Bewertungstext'), 'type'=>'textarea', 'rows'=>4, 'values'=>[]])
                <div class="mb-3">
                    <label>{{ __('Reihenfolge') }}</label>
                    <input type="number" class="form-control" name="sort_order" value="0">
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_placeholder" name="is_placeholder" value="1" checked>
                    <label class="form-check-label" for="is_placeholder">{{ __('Als Beispiel kennzeichnen (noch keine echte Bewertung)') }}</label>
                </div>
                <button type="submit" class="btn btn-primary">{{ __('Speichern') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
