@extends('dashboard.layouts.layout')
@section('content')
<div class="wrapper"><div class="page"><div class="page-inner">
<h1 class="h3">{{ __('Add Shipment') }}</h1>
@include('dashboard.partials.feedback')
<div class="card"><div class="card-body"><form action="{{ route('dashboard.landing.shipments.store') }}" method="POST">
    @csrf
    @include('dashboard.landing.shipments._fields')
    <button class="btn btn-primary">{{ __('Save') }}</button>
</form></div></div>
</div></div></div>
@endsection
