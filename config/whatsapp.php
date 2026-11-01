<?php

return [
    'token' => env('WHATSAPP_ACCESS_TOKEN'),
    'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
    'version' => env('WHATSAPP_GRAPH_VERSION'),
    'template' => env('WHATSAPP_ACCOUNT_TEMPLATE'),
    'language' => env('WHATSAPP_TEMPLATE_LANGUAGE', 'ar'),
    'business_id' => env('WHATSAPP_BUSINESS_ID'),
    'app_secret' => env('WHATSAPP_APP_SECRET'),
    'verify_token' => env('WHATSAPP_VERIFY_TOKEN'),
    'chrome_binary' => env('CHROME_BINARY'),
    'chrome_no_sandbox' => env('CHROME_NO_SANDBOX', false),
    // Phase 3 deferred. Keep all automatic notification processing paused.
    // 'paused' => env('WHATSAPP_NOTIFICATIONS_PAUSED', false),
    'paused' => true,
    'conversation_days' => (int) env('WHATSAPP_CONVERSATION_DAYS', 90),
];
