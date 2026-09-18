@extends('dashboard.layouts.layout')
@section('content')
<div class="wrapper">
    <div class="page">
        <div class="page-inner">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <p class="text-muted">{{ __('Wählen Sie, welche Sprachen Besucher auf der Website auswählen können. Die Standardsprache kann nicht deaktiviert werden.') }}</p>
            <form action="{{ route('dashboard.landing.languages.update') }}" method="POST">
                @csrf
                @foreach($languages as $language)
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input"
                               id="lang-{{ $language->code }}"
                               name="enabled[{{ $language->code }}]" value="1"
                               {{ $language->is_enabled ? 'checked' : '' }}
                               {{ $language->is_default ? 'disabled' : '' }}>
                        <label class="form-check-label" for="lang-{{ $language->code }}">
                            {{ $language->native_name }} ({{ $language->name }}, {{ strtoupper($language->code) }})
                            @if($language->is_default)
                                <span class="badge bg-secondary">{{ __('Standard') }}</span>
                            @endif
                        </label>
                        @if($language->is_default)
                            {{-- Disabled checkboxes aren't submitted; keep the default always enabled. --}}
                            <input type="hidden" name="enabled[{{ $language->code }}]" value="1">
                        @endif
                    </div>
                @endforeach
                <button type="submit" class="btn btn-primary">{{ __('Speichern') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
