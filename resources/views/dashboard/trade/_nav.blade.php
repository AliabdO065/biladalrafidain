@include('dashboard.partials.feedback')
@if(!app(\App\Services\WhatsAppMessaging::class)->configured())<div class="alert alert-info">{{ __('trade.not_configured') }}</div>@endif
