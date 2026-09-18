@extends('dashboard.layouts.layout')
@section('content')
<div class="wrapper">
    <div class="page">
        <div class="page-inner">
            <form action="{{ route('dashboard.landing.policies.update', $item->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label>{{ __('Slug') }}</label>
                    <input type="text" class="form-control" name="slug" value="{{ $item->slug }}" required>
                </div>
                @include('dashboard.landing.partials._translatable-field', ['name'=>'title', 'label'=>__('Title'), 'values'=>$item->translationsFor('title')])
                @include('dashboard.landing.partials._translatable-field', ['name'=>'body', 'label'=>__('Body'), 'type'=>'textarea', 'rows'=>8, 'values'=>$item->translationsFor('body'), 'required'=>false])
                <div class="mb-3">
                    <label>{{ __('Sort order') }}</label>
                    <input type="number" class="form-control" style="width:120px" name="sort_order" value="{{ $item->sort_order }}">
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ $item->is_active ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">{{ __('Active') }}</label>
                </div>
                <button type="submit" class="btn btn-primary">{{ __('Speichern') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
