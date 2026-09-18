@if($settings && $settings->alert_banner_active && $settings->alert_banner_text)
<div class="lk-alert">
    <div class="lk-container">
        <i class="fa-solid fa-triangle-exclamation"></i>{{ $settings->alert_banner_text }}
    </div>
</div>
@endif
