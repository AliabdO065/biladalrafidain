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
            <form action="{{ route('dashboard.landing.stats.update', $item->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label>{{ __('Wert') }}</label>
                    <input type="text" class="form-control" name="value" value="{{ $item->value }}" required>
                </div>
                @include('dashboard.landing.partials._translatable-field', ['name'=>'label', 'label'=>__('Beschriftung'), 'values'=>$item->translationsFor('label')])
                <div class="mb-3">
                    <label>{{ __('Icon') }}</label>
                    <input type="text" class="form-control" name="icon" value="{{ $item->icon }}">
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
