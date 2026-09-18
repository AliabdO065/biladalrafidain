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
            <form action="{{ route('dashboard.landing.faqs.store') }}" method="POST">
                @csrf
                @include('dashboard.landing.partials._translatable-field', ['name'=>'question', 'label'=>__('Frage'), 'values'=>[]])
                @include('dashboard.landing.partials._translatable-field', ['name'=>'answer', 'label'=>__('Antwort'), 'type'=>'textarea', 'rows'=>4, 'values'=>[]])
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
