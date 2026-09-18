@extends('dashboard.layouts.layout')
@php
    $problemTypeLabels = [
        'shipping' => __('Shipping'),
        'customs_clearance' => __('Customs Clearance'),
        'tracking_issue' => __('Tracking Issue'),
        'other' => __('Sonstiges'),
    ];
@endphp
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
                            @include('dashboard.landing.partials._translatable-field', ['name'=>'callback_eyebrow', 'label'=>__('Kicker'), 'values'=>$settings->translationsFor('callback_eyebrow'), 'required'=>false])
                            @include('dashboard.landing.partials._translatable-field', ['name'=>'callback_heading', 'label'=>__('Überschrift'), 'values'=>$settings->translationsFor('callback_heading'), 'required'=>false])
                            @include('dashboard.landing.partials._translatable-field', ['name'=>'callback_subtext', 'label'=>__('Untertext'), 'values'=>$settings->translationsFor('callback_subtext'), 'required'=>false])
                            <button type="submit" class="btn btn-primary">{{ __('Speichern') }}</button>
                        </form>
                    </div>
                </div>
            </div>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>{{ __('Datum') }}</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Telefon') }}</th>
                        <th>{{ __('Destination') }}</th>
                        <th>{{ __('Anliegen') }}</th>
                        <th>{{ __('E-Mail') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td>{{ $item->created_at->format('d.m.Y H:i') }}</td>
                            <td>{{ $item->name }}</td>
                            <td><a href="tel:{{ $item->phone }}">{{ $item->phone }}</a></td>
                            <td>{{ $item->postal_code }}</td>
                            <td>{{ $problemTypeLabels[$item->problem_type] ?? $item->problem_type }}</td>
                            <td>{{ $item->email }}</td>
                            <td>
                                <a href="{{ route('dashboard.landing.leads.delete', $item->id) }}" class="btn btn-danger btn-sm delete-confirm">{{ __('Löschen') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">{{ __('Noch keine Rückruf-Anfragen.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $items->links() }}
        </div>
    </div>
</div>
@endsection
