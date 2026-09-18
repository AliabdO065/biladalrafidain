@extends('dashboard.layouts.layout')
@section('content')
<div class="wrapper">
    <div class="page">
        <div class="page-inner">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card mb-4">
                <div class="card-body">
                    <form action="{{ route('dashboard.landing.shipments.update', $item->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label>{{ __('Tracking Code') }}</label>
                            <input type="text" class="form-control" name="tracking_code" value="{{ $item->tracking_code }}" required>
                        </div>
                        <div class="mb-3">
                            <label>{{ __('Sender') }}</label>
                            <input type="text" class="form-control" name="sender_name" value="{{ $item->sender_name }}">
                        </div>
                        <div class="mb-3">
                            <label>{{ __('Receiver') }}</label>
                            <input type="text" class="form-control" name="receiver_name" value="{{ $item->receiver_name }}">
                        </div>
                        <div class="mb-3">
                            <label>{{ __('Origin') }}</label>
                            <input type="text" class="form-control" name="origin" value="{{ $item->origin }}">
                        </div>
                        <div class="mb-3">
                            <label>{{ __('Destination') }}</label>
                            <input type="text" class="form-control" name="destination" value="{{ $item->destination }}">
                        </div>
                        <div class="mb-3">
                            <label>{{ __('Status') }}</label>
                            <select class="form-control" name="status">
                                @foreach(['received'=>__('Received at warehouse'),'customs'=>__('In customs clearance'),'in_transit'=>__('In transit'),'out_for_delivery'=>__('Out for delivery'),'delivered'=>__('Delivered'),'on_hold'=>__('On hold')] as $value => $label)
                                    <option value="{{ $value }}" {{ $item->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>{{ __('Estimated delivery') }}</label>
                            <input type="date" class="form-control" style="width:200px" name="estimated_delivery" value="{{ optional($item->estimated_delivery)->format('Y-m-d') }}">
                        </div>
                        <button type="submit" class="btn btn-primary">{{ __('Speichern') }}</button>
                    </form>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">{{ __('Tracking timeline') }}</div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Note') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($item->events as $event)
                                <tr>
                                    <td>{{ $event->happened_at->format('d.m.Y H:i') }}</td>
                                    <td>{{ $event->status }}</td>
                                    <td>{{ $event->note }}</td>
                                    <td>
                                        <a href="{{ route('dashboard.landing.shipments.events.delete', [$item->id, $event->id]) }}" class="btn btn-danger btn-sm delete-confirm">{{ __('Löschen') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4">{{ __('No tracking events yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>

                    <form action="{{ route('dashboard.landing.shipments.events.store', $item->id) }}" method="POST" class="mt-3">
                        @csrf
                        <div class="mb-3">
                            <label>{{ __('Status') }}</label>
                            <select class="form-control" name="status">
                                @foreach(['received'=>__('Received at warehouse'),'customs'=>__('In customs clearance'),'in_transit'=>__('In transit'),'out_for_delivery'=>__('Out for delivery'),'delivered'=>__('Delivered'),'on_hold'=>__('On hold')] as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>{{ __('Date/time') }}</label>
                            <input type="datetime-local" class="form-control" style="width:260px" name="happened_at" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                        </div>
                        @include('dashboard.landing.partials._translatable-field', ['name'=>'note', 'label'=>__('Note'), 'values'=>[], 'required'=>false])
                        <button type="submit" class="btn btn-success">{{ __('Add Event') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
