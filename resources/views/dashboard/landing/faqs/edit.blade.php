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
            <form action="{{ route('dashboard.landing.faqs.update', $item->id) }}" method="POST">
                @csrf
                @include('dashboard.landing.partials._translatable-field', ['name'=>'question', 'label'=>__('Frage'), 'values'=>$item->translationsFor('question')])
                @include('dashboard.landing.partials._translatable-field', ['name'=>'answer', 'label'=>__('Antwort'), 'type'=>'textarea', 'rows'=>4, 'values'=>$item->translationsFor('answer')])
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
