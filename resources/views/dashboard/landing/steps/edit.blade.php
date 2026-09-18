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
            <form action="{{ route('dashboard.landing.steps.update', $item->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label>{{ __('Schrittnummer') }}</label>
                    <input type="number" class="form-control" name="step_number" value="{{ $item->step_number }}" min="1" required>
                </div>
                @include('dashboard.landing.partials._translatable-field', ['name'=>'title', 'label'=>__('Titel'), 'values'=>$item->translationsFor('title')])
                @include('dashboard.landing.partials._translatable-field', ['name'=>'description', 'label'=>__('Beschreibung'), 'type'=>'textarea', 'values'=>$item->translationsFor('description'), 'required'=>false])
                <div class="mb-3 form-inline">
                    <label>{{ __('Bild') }}</label>
                    <input type="file" class="form-control" name="image">
                    @if($item->image)
                        <img src="{{ asset($item->image) }}" style="width:100px;margin-left:20px;" alt="{{ $item->title }}">
                    @endif
                </div>
                <div class="mb-3">
                    <label>{{ __('Reihenfolge') }}</label>
                    <input type="number" class="form-control" name="sort_order" value="{{ $item->sort_order }}">
                </div>
                <button type="submit" class="btn btn-primary">{{ __('Aktualisieren') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
