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
            <form action="{{ route('dashboard.landing.stats.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label>{{ __('Wert (z.B. "45+")') }}</label>
                    <input type="text" class="form-control" name="value" required>
                </div>
                @include('dashboard.landing.partials._translatable-field', ['name'=>'label', 'label'=>__('Beschriftung'), 'values'=>[]])
                <div class="mb-3">
                    <label>{{ __('Icon (Font Awesome Klasse, z.B. fa-award)') }}</label>
                    <input type="text" class="form-control" name="icon">
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
