<svg class="mk-flag" viewBox="0 0 36 24" width="30" height="20" role="img" aria-label="{{ __('market.'.$country) }}">
    <path fill="#fff" d="M0 0h36v24H0z"/><path fill="#ce2534" d="M0 0h36v8H0z"/><path fill="#151b24" d="M0 16h36v8H0z"/>
    @if($country === 'egypt')
        <path fill="#bf942c" d="m15 10 3 1 3-1-1 4h-4zM17 9h2v6h-2z"/>
    @else
        <text x="18" y="14" text-anchor="middle" fill="#078148" font-size="6" font-family="Arial,sans-serif" font-weight="700">الله أكبر</text>
    @endif
</svg>
