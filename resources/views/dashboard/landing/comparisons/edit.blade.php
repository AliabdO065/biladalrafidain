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
            <form action="{{ route('dashboard.landing.comparisons.update', $item->id) }}" method="POST">
                @csrf
                @include('dashboard.landing.partials._translatable-field', ['name'=>'criterion', 'label'=>__('Kriterium'), 'values'=>$item->translationsFor('criterion')])
                @include('dashboard.landing.partials._translatable-field', ['name'=>'us_value', 'label'=>__('Unser Wert'), 'values'=>$item->translationsFor('us_value')])
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="us_is_positive" name="us_is_positive" value="1" {{ $item->us_is_positive ? 'checked' : '' }}>
                    <label class="form-check-label" for="us_is_positive">{{ __('Positiv (✅)') }}</label>
                </div>
                @include('dashboard.landing.partials._translatable-field', ['name'=>'them_value', 'label'=>__('Wert der Konkurrenz'), 'values'=>$item->translationsFor('them_value')])
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="them_is_positive" name="them_is_positive" value="1" {{ $item->them_is_positive ? 'checked' : '' }}>
                    <label class="form-check-label" for="them_is_positive">{{ __('Positiv (✅)') }}</label>
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
