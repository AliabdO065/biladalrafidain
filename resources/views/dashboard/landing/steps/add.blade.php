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
            <form action="{{ route('dashboard.landing.steps.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label>{{ __('Schrittnummer') }}</label>
                    <input type="number" class="form-control" name="step_number" value="1" min="1" required>
                </div>
                @include('dashboard.landing.partials._translatable-field', ['name'=>'title', 'label'=>__('Titel'), 'values'=>[]])
                @include('dashboard.landing.partials._translatable-field', ['name'=>'description', 'label'=>__('Beschreibung'), 'type'=>'textarea', 'values'=>[], 'required'=>false])
                <div class="mb-3">
                    <label>{{ __('Bild') }}</label>
                    <input type="file" class="form-control" name="image">
                </div>
                <div class="mb-3">
                    <label>{{ __('Reihenfolge') }}</label>
                    <input type="number" class="form-control" name="sort_order" value="0">
                </div>
                <button type="submit" class="btn btn-primary">{{ __('Speichern') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
