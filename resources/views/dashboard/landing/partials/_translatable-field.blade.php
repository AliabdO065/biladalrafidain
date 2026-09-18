@php
    // Expects: $name (input base name, e.g. "title"), $label, $values (assoc array
    // code=>text), optional $type ('text'|'textarea'), $rows, $required (bool, applies to de only).
    $type = $type ?? 'text';
    $rows = $rows ?? 3;
    $required = $required ?? true;
    $uid = $name . '-' . uniqid();
    $langs = [
        'ar' => 'العربية',
        'en' => 'English',
        'tr' => 'Türkçe',
        'he' => 'עברית',
    ];
@endphp
<div class="mb-3">
    <label>{{ $label }}</label>
    <ul class="nav nav-tabs" role="tablist">
        @foreach($langs as $code => $langLabel)
            <li class="nav-item" role="presentation">
                <button class="nav-link @if($loop->first) active @endif" type="button"
                        data-bs-toggle="tab" data-bs-target="#{{ $uid }}-{{ $code }}">
                    {{ $langLabel }}@if($code === 'ar' && $required) *@endif
                </button>
            </li>
        @endforeach
    </ul>
    <div class="tab-content border border-top-0 p-2">
        @foreach($langs as $code => $langLabel)
            <div class="tab-pane fade @if($loop->first) show active @endif" id="{{ $uid }}-{{ $code }}">
                @if($type === 'textarea')
                    <textarea class="form-control" rows="{{ $rows }}"
                              name="{{ $name }}[{{ $code }}]">{{ $values[$code] ?? '' }}</textarea>
                @else
                    <input type="text" class="form-control"
                           name="{{ $name }}[{{ $code }}]" value="{{ $values[$code] ?? '' }}">
                @endif
            </div>
        @endforeach
    </div>
</div>
