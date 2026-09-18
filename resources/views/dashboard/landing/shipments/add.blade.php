@extends('dashboard.layouts.layout')
@section('content')
<div class="wrapper">
    <div class="page">
        <div class="page-inner">
            <form action="{{ route('dashboard.landing.shipments.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label>{{ __('Tracking Code') }}</label>
                    <input type="text" class="form-control" name="tracking_code" value="{{ old('tracking_code') }}" required>
                </div>
                <div class="mb-3">
                    <label>{{ __('Sender') }}</label>
                    <input type="text" class="form-control" name="sender_name" value="{{ old('sender_name') }}">
                </div>
                <div class="mb-3">
                    <label>{{ __('Receiver') }}</label>
                    <input type="text" class="form-control" name="receiver_name" value="{{ old('receiver_name') }}">
                </div>
                <div class="mb-3">
                    <label>{{ __('Origin') }}</label>
                    <input type="text" class="form-control" name="origin" value="{{ old('origin') }}">
                </div>
                <div class="mb-3">
                    <label>{{ __('Destination') }}</label>
                    <input type="text" class="form-control" name="destination" value="{{ old('destination') }}">
                </div>
                <div class="mb-3">
                    <label>{{ __('Status') }}</label>
                    <select class="form-control" name="status">
                        <option value="received">{{ __('Received at warehouse') }}</option>
                        <option value="customs">{{ __('In customs clearance') }}</option>
                        <option value="in_transit">{{ __('In transit') }}</option>
                        <option value="out_for_delivery">{{ __('Out for delivery') }}</option>
                        <option value="delivered">{{ __('Delivered') }}</option>
                        <option value="on_hold">{{ __('On hold') }}</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>{{ __('Estimated delivery') }}</label>
                    <input type="date" class="form-control" style="width:200px" name="estimated_delivery">
                </div>
                <button type="submit" class="btn btn-primary">{{ __('Speichern') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
