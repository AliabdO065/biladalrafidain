<svg class="auth-icon {{ $class ?? '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
@switch($icon)
    @case('ship')<path d="M3 12l9-3 9 3-2 6H5l-2-6zM7 10V5h10v5M10 5V2h4v3M2 21c2 0 2-1 4-1s2 1 4 1 2-1 4-1 2 1 4 1 2-1 4-1"/>@break
    @case('mail')<rect x="3" y="5" width="18" height="14" rx="3"/><path d="M4 7l8 6 8-6"/>@break
    @case('lock')<rect x="5" y="10" width="14" height="11" rx="3"/><path d="M8 10V7a4 4 0 018 0v3M12 14v3"/>@break
    @case('arrow')<path d="M5 12h14m-5-5l5 5-5 5"/>@break
    @case('eye')<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>@break
    @case('eye-off')<path d="M3 3l18 18M10.6 5.1A12 12 0 0112 5c6.5 0 10 7 10 7a18 18 0 01-3 3.8M6.5 6.5A19 19 0 002 12s3.5 7 10 7a12 12 0 005.5-1.5M10 10a3 3 0 004 4"/>@break
    @case('box')<path d="M3 7l9-5 9 5v10l-9 5-9-5V7zM3 7l9 5 9-5M12 12v10M7.5 4.5l9 5"/>@break
    @case('route')<circle cx="5" cy="5" r="3"/><circle cx="19" cy="19" r="3"/><path d="M8 5h7a4 4 0 010 8H9a4 4 0 000 8h7"/>@break
    @case('account')<path d="M6 3h12v18H6zM9 7h6M9 11h6M9 15h2M14 15h1M9 18h2M14 18h1"/>@break
@endswitch
</svg>
