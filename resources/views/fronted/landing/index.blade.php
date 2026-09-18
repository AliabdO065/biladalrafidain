@extends('fronted.layouts.landing')

@section('content')
    @include('fronted.landing.partials._navbar')

    @include('fronted.landing.partials._alert-banner')

<div style="
    background-color: rgb(99, 97, 6);
    height: 380px;
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    text-align: center;
">
    <h1 style="
        color: rgb(248, 244, 244);
        font-size: 32px;
        font-weight: 600;
        letter-spacing: 1px;
        margin: 0;
    ">
        شركة بلاد الرافدين للنقل الدولي
    </h1>
</div>
    
    {{-- @include('fronted.landing.partials._hero') --}}
    {{-- @include('fronted.landing.partials._stats') --}}
    {{-- @include('fronted.landing.partials._services') --}}
    {{-- @include('fronted.landing.partials._steps') --}}
    {{-- @include('fronted.landing.partials._about') --}}
    {{-- @include('fronted.landing.partials._comparison') --}} 
    {{-- @include('fronted.landing.partials._reviews') --}}
    {{-- @include('fronted.landing.partials._faq') --}}
    {{-- @include('fronted.landing.partials._callback-form') --}}
    @include('fronted.landing.partials._footer')
    @include('fronted.landing.partials._sticky-bar')
@endsection