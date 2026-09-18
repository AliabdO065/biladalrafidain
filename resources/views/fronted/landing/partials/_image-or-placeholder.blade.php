@php
    $src = $src ?? null;
    $icon = $icon ?? 'fa-image';
    $label = $label ?? '';
    $class = $class ?? '';
@endphp
@if($src)
    <img src="{{ asset($src) }}" alt="{{ $label }}" class="{{ $class }}">
@else
    <div class="lk-placeholder {{ $class }}">
        <i class="fa-solid {{ $icon }}"></i>
        @if($label)
            <span>{{ $label }}</span>
        @endif
    </div>
@endif
