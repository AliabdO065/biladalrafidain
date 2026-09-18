@extends('dashboard.layouts.layout')
@section('content')
<div class="wrapper">
    <div class="page">
        <div class="page-inner">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <a href="{{ route('dashboard.landing.shipments.add') }}" class="btn btn-success">{{ __('Add Shipment') }}</a>
            <hr>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>{{ __('Tracking Code') }}</th>
                        <th>{{ __('Sender') }}</th>
                        <th>{{ __('Receiver') }}</th>
                        <th>{{ __('Route') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td><code>{{ $item->tracking_code }}</code></td>
                            <td>{{ $item->sender_name }}</td>
                            <td>{{ $item->receiver_name }}</td>
                            <td>{{ $item->origin }} &rarr; {{ $item->destination }}</td>
                            <td><span class="badge bg-info">{{ $item->status }}</span></td>
                            <td>
                                <a href="{{ route('dashboard.landing.shipments.edit', $item->id) }}" class="btn btn-primary btn-sm">{{ __('Bearbeiten') }}</a>
                                <a href="{{ route('dashboard.landing.shipments.delete', $item->id) }}" class="btn btn-danger btn-sm delete-confirm">{{ __('Löschen') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">{{ __('No shipments yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
