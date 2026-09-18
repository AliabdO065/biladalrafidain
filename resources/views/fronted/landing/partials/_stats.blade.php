<div class="lk-container">
    <div class="lk-stats">
        @foreach($stats as $stat)
            <div class="lk-stat">
                <i class="fa-solid {{ $stat->icon ?: 'fa-star' }}"></i>
                <span class="val">{{ $stat->value }}</span>
                <span class="lbl">{{ $stat->label }}</span>
            </div>
        @endforeach
    </div>
</div>
