@extends('dashboard.layouts.layout')
@section('content')
<div class="wrapper">
    <div class="page">
        <div class="page-inner">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <a href="{{ route('dashboard.landing.policies.add') }}" class="btn btn-success">{{ __('Add Policy') }}</a>
            <hr>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>{{ __('Slug') }}</th>
                        <th>{{ __('Title') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td><code>{{ $item->slug }}</code></td>
                            <td>{{ $item->title }}</td>
                            <td>@if($item->is_active)<span class="badge bg-success">{{ __('Active') }}</span>@else<span class="badge bg-secondary">{{ __('inaktiv') }}</span>@endif</td>
                            <td>
                                <a href="{{ route('dashboard.landing.policies.edit', $item->id) }}" class="btn btn-primary btn-sm">{{ __('Bearbeiten') }}</a>
                                <a href="{{ route('dashboard.landing.policies.delete', $item->id) }}" class="btn btn-danger btn-sm delete-confirm">{{ __('Löschen') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4">{{ __('No policies yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
