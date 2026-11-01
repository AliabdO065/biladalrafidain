<div class="ops-row-actions">
    <a class="ops-action-main" href="{{ route('dashboard.accounts.merchant', array_merge(\Illuminate\Support\Arr::except($filters, ['search','bucket']), ['key' => $merchant->account_key, 'bucket' => $merchant->currency ?? ''])) }}"><i class="oi oi-eye" aria-hidden="true"></i>{{ __('Review account') }}</a>
</div>
