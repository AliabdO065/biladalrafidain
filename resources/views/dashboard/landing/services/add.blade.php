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
            <form action="{{ route('dashboard.landing.services.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @include('dashboard.landing.partials._translatable-field', ['name'=>'title', 'label'=>__('Titel'), 'values'=>[]])
                @include('dashboard.landing.partials._translatable-field', ['name'=>'description', 'label'=>__('Beschreibung'), 'type'=>'textarea', 'values'=>[], 'required'=>false])
                <div class="mb-3">
                    <label>{{ __('Icon (Font Awesome Klasse)') }}</label>
                    <input type="text" class="form-control" name="icon">
                </div>
                <div class="mb-3">
                    <label>{{ __('Bild') }}</label>
                    <input type="file" class="form-control" name="image">
                </div>
                <div class="mb-3">
                    <label>{{ __('Reihenfolge') }}</label>
                    <input type="number" class="form-control" name="sort_order" value="0">
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" checked>
                    <label class="form-check-label" for="is_active">{{ __('Aktiv (auf der Website sichtbar)') }}</label>
                </div>
                <button type="submit" class="btn btn-primary">{{ __('Speichern') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
