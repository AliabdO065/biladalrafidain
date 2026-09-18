@extends('dashboard.layouts.layout')
@section('content')
<div class="wrapper">
    <div class="page">
        <div class="page-inner">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('dashboard.landing.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="card mb-4">
                    <button class="card-header lk-collapse-toggle collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-about-heading" aria-expanded="false" aria-controls="collapse-about-heading">
                        <span><i class="fa-solid fa-heading"></i> {{ __('Abschnitts-Überschrift') }}</span>
                        <i class="fa-solid fa-chevron-down lk-collapse-caret"></i>
                    </button>
                    <div class="collapse" id="collapse-about-heading">
                        <div class="card-body">
                            @include('dashboard.landing.partials._translatable-field', ['name'=>'about_eyebrow', 'label'=>__('Kicker (kleiner Text über der Überschrift)'), 'values'=>$settings->translationsFor('about_eyebrow'), 'required'=>false])
                            @include('dashboard.landing.partials._translatable-field', ['name'=>'about_heading', 'label'=>__('Überschrift'), 'values'=>$settings->translationsFor('about_heading'), 'required'=>false])
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <button class="card-header lk-collapse-toggle collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-about-owner" aria-expanded="false" aria-controls="collapse-about-owner">
                        <span><i class="fa-solid fa-user"></i> {{ __('Inhaber') }}</span>
                        <i class="fa-solid fa-chevron-down lk-collapse-caret"></i>
                    </button>
                    <div class="collapse" id="collapse-about-owner">
                        <div class="card-body">
                            <div class="mb-3">
                                <label>{{ __('Name Inhaber') }}</label>
                                <input type="text" class="form-control" name="about_owner_name" value="{{ $settings->about_owner_name }}">
                            </div>
                            <div class="mb-3 form-inline">
                                <label>{{ __('Foto Inhaber') }}</label>
                                <input type="file" class="form-control" name="about_owner_photo_file">
                                @if($settings->about_owner_photo)
                                    <img src="{{ asset($settings->about_owner_photo) }}" style="width:80px;margin-left:20px;" alt="Owner">
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <button class="card-header lk-collapse-toggle collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-about-story" aria-expanded="false" aria-controls="collapse-about-story">
                        <span><i class="fa-solid fa-book"></i> {{ __('Firmengeschichte') }}</span>
                        <i class="fa-solid fa-chevron-down lk-collapse-caret"></i>
                    </button>
                    <div class="collapse" id="collapse-about-story">
                        <div class="card-body">
                            @include('dashboard.landing.partials._translatable-field', ['name'=>'about_story', 'label'=>__('Firmengeschichte'), 'type'=>'textarea', 'rows'=>4, 'values'=>$settings->translationsFor('about_story'), 'required'=>false])
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <button class="card-header lk-collapse-toggle collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-about-trust" aria-expanded="false" aria-controls="collapse-about-trust">
                        <span><i class="fa-solid fa-circle-check"></i> {{ __('Trust-Punkte') }}</span>
                        <i class="fa-solid fa-chevron-down lk-collapse-caret"></i>
                    </button>
                    <div class="collapse" id="collapse-about-trust">
                        <div class="card-body">
                            @for($i = 1; $i <= 4; $i++)
                                @include('dashboard.landing.partials._translatable-field', ['name'=>"trust_{$i}_title", 'label'=>__('Trust-Punkt :n: Titel', ['n' => $i]), 'values'=>$settings->translationsFor("trust_{$i}_title"), 'required'=>false])
                                @include('dashboard.landing.partials._translatable-field', ['name'=>"trust_{$i}_text", 'label'=>__('Trust-Punkt :n: Text', ['n' => $i]), 'values'=>$settings->translationsFor("trust_{$i}_text"), 'required'=>false])
                            @endfor
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary lk-settings-save">{{ __('Speichern') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
