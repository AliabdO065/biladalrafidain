@extends('dashboard.layouts.layout')
@section('content')
<div class="wrapper">
    <div class="page">
        <div class="page-inner">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card mb-4">
                <button class="card-header lk-collapse-toggle collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#heading-form-body" aria-expanded="false" aria-controls="heading-form-body">
                    <span><i class="fa-solid fa-heading"></i> {{ __('Abschnitts-Überschrift') }}</span>
                    <i class="fa-solid fa-chevron-down lk-collapse-caret"></i>
                </button>
                <div class="collapse" id="heading-form-body">
                    <div class="card-body">
                        <form action="{{ route('dashboard.landing.settings.update') }}" method="POST">
                            @csrf
                            @include('dashboard.landing.partials._translatable-field', ['name'=>'comparison_eyebrow', 'label'=>__('Kicker'), 'values'=>$settings->translationsFor('comparison_eyebrow'), 'required'=>false])
                            @include('dashboard.landing.partials._translatable-field', ['name'=>'comparison_heading', 'label'=>__('Überschrift'), 'values'=>$settings->translationsFor('comparison_heading'), 'required'=>false])
                            @include('dashboard.landing.partials._translatable-field', ['name'=>'comparison_subheading', 'label'=>__('Untertext'), 'values'=>$settings->translationsFor('comparison_subheading'), 'required'=>false])
                            <button type="submit" class="btn btn-primary">{{ __('Speichern') }}</button>
                        </form>
                    </div>
                </div>
            </div>

            <a href="{{ route('dashboard.landing.comparisons.add') }}" class="btn btn-success">{{ __('Zeile hinzufügen') }}</a>
            <hr>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>{{ __('Kriterium') }}</th>
                        <th>{{ __('Wir') }}</th>
                        <th>{{ __('Konkurrenz') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <td>{{ $item->criterion }}</td>
                            <td>{{ $item->us_value }} {!! $item->us_is_positive ? '✅' : '❌' !!}</td>
                            <td>{{ $item->them_value }} {!! $item->them_is_positive ? '✅' : '❌' !!}</td>
                            <td>
                                <a href="{{ route('dashboard.landing.comparisons.edit', $item->id) }}" class="btn btn-primary btn-sm">{{ __('Bearbeiten') }}</a>
                                <a href="{{ route('dashboard.landing.comparisons.delete', $item->id) }}" class="btn btn-danger btn-sm delete-confirm">{{ __('Löschen') }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
