@extends('dashboard.layouts.layout')
@section('content')
<div class="wrapper">
    <div class="page">
        <div class="page-inner">
            <form action="{{ route('dashboard.landing.policies.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label>{{ __('Slug') }} <small class="text-muted">({{ __('used in the page link, e.g. privacy-policy') }})</small></label>
                    <input type="text" class="form-control" name="slug" value="{{ old('slug') }}" required>
                </div>
                @include('dashboard.landing.partials._translatable-field', ['name'=>'title', 'label'=>__('Title'), 'values'=>[]])
                @include('dashboard.landing.partials._translatable-field', ['name'=>'body', 'label'=>__('Body'), 'type'=>'textarea', 'rows'=>8, 'values'=>[], 'required'=>false])
                <div class="mb-3">
                    <label>{{ __('Sort order') }}</label>
                    <input type="number" class="form-control" style="width:120px" name="sort_order" value="0">
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" checked>
                    <label class="form-check-label" for="is_active">{{ __('Active') }}</label>
                </div>
                <button type="submit" class="btn btn-primary">{{ __('Speichern') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
