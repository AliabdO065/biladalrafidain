@extends('dashboard.layouts.layout')
@section('content')
<div class="wrapper"><div class="page"><div class="page-inner operations-detail-page">
    <a href="{{ route('dashboard.accounts.index', \Illuminate\Support\Arr::except($filters, ['bucket'])) }}">{{ __('Merchant Accounts') }}</a>
    <header class="operations-page-header mt-3"><div><h1 class="h3">{{ $summary['name'] }}</h1><p class="text-muted mb-0">{{ $period }} · {{ $summary['currency'] ?: __('Unspecified currency') }}</p></div><button type="button" class="btn btn-outline-primary" onclick="window.print()">{{ __('Print this page') }}</button></header>
    @include('dashboard.partials.feedback')
    @if($accountContact)<div class="flow-context"><div><strong>{{ __('flow.merchant_file') }}</strong><small>{{ __('flow.contact_scope') }}</small></div><div class="operations-row-actions"><a class="btn btn-light" href="{{ route('dashboard.trade.movements',$accountContact) }}">{{ __('trade.movements') }}</a><a class="btn btn-outline-primary" href="{{ route('dashboard.trade.messages',['contact_id'=>$accountContact->id]) }}">{{ __('flow.contact_messages') }}</a></div></div>@endif
    <div class="card mb-4 account-no-print"><div class="card-body">@include('dashboard.accounts._filters', ['filterAction' => route('dashboard.accounts.merchant', $key)])</div></div>
    <div class="account-metrics">
        @foreach(['count' => __('Shipments'), 'fee' => __('Shipping Fee'), 'paid' => __('Amount Collected'), 'balance' => __('Remaining Balance')] as $field => $label)
        <div class="card"><div class="card-body"><span class="text-muted">{{ $label }}</span><strong>{{ $summary[$field] }}</strong>@if($field !== 'count')<small>{{ $summary['currency'] ?: __('Unspecified currency') }}</small>@endif</div></div>
        @endforeach
    </div>
    <p class="text-muted account-calculation-note">{{ __('Fees and collections are summed from the selected shipments. The remaining amount uses each shipment balance, or fees minus collections when its balance is empty. The report Total column is not added.') }}</p>
    @if($summary['incomplete'])<div class="alert alert-warning">{{ __(':count shipments have incomplete financial data. Complete their shipping fees and collected amounts before sending.', ['count' => $summary['incomplete']]) }}</div>@endif
    @if($summary['phone_count'] > 1)<div class="alert alert-warning">{{ __('These shipments contain different phone numbers. Confirm the recipient before sending.') }}</div>@endif
    <div class="card mb-4 account-no-print"><div class="card-body">
        <h2 class="h5">{{ __('Send this merchant statement') }}</h2>
        @include('dashboard.accounts._connection')
        <form method="POST" action="{{ route('dashboard.accounts.prepare', $key) }}">
            @csrf
            @foreach(\Illuminate\Support\Arr::except($filters, ['search']) as $field => $value)<input type="hidden" name="{{ $field }}" value="{{ $value }}">@endforeach
            <input type="hidden" name="version" value="{{ $summary['version'] }}">
            <div class="row">
                <div class="col-md-4 mb-3"><label for="statement-phone">{{ __('WhatsApp number') }}</label><input id="statement-phone" dir="ltr" class="form-control" type="tel" name="phone" value="{{ old('phone', $summary['phone']) }}" required maxlength="50"></div>
                <div class="col-md-4 mb-3"><label for="statement-country">{{ __('Country for local numbers') }}</label><select id="statement-country" class="form-control" name="country_code"><option value="">{{ __('International number already includes country code') }}</option><option value="964" @selected(old('country_code') === '964')>{{ __('Iraq') }} +964</option><option value="20" @selected(old('country_code') === '20')>{{ __('Egypt') }} +20</option></select></div>
                <div class="col-md-4 mb-3"><label for="statement-currency">{{ __('Currency') }}</label>
                    @if($summary['currency'])<input id="statement-currency" class="form-control" name="currency" value="{{ $summary['currency'] }}" readonly>
                    @else<select id="statement-currency" class="form-control" name="currency" required><option value="">{{ __('Choose the currency of these amounts') }}</option>@foreach(['EGP','IQD','USD','EUR','SAR','AED','TRY'] as $currency)<option value="{{ $currency }}" @selected(old('currency') === $currency)>{{ $currency }}</option>@endforeach</select>@endif
                </div>
            </div>
            <p class="text-muted">{{ __('Enter the number with its country code, or select the country if it is local. No country or currency is assumed automatically.') }}</p>
            <label class="d-block mb-3"><input type="checkbox" name="reviewed" value="1" required> {{ __('I reviewed the shipments, amounts, currency and recipient number.') }}</label>
            <div class="operations-row-actions">
                <button class="btn btn-outline-success" name="action" value="manual" formtarget="_blank" @disabled($summary['incomplete'] > 0)>{{ __('Open statement in WhatsApp') }}</button>
                {{-- Automatic sending retained for a future phase.
                <button class="btn btn-primary" name="action" value="queue" @disabled(!$configured || $summary['incomplete'] > 0)>{{ __('Send using WhatsApp service') }}</button>
                --}}
            </div>
            <p class="text-muted mt-3 mb-0">{{ __('manual.mode') }}</p>
        </form>
    </div></div>
    <div class="operations-section-header"><h2 class="h5">{{ __('Shipments included in this account') }}</h2></div>
    <p class="text-muted">{{ __('Showing shipments :from to :to of :total.', ['from' => $shipments->firstItem(), 'to' => $shipments->lastItem(), 'total' => $shipments->total()]) }}</p>
    <div class="card"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>{{ __('Tracking Code') }}</th><th>{{ __('Trip') }}</th><th>{{ __('Receipt Date') }}</th><th>{{ __('Shipping Fee') }}</th><th>{{ __('Amount Collected') }}</th><th>{{ __('Remaining Balance') }}</th><th class="account-no-print"></th></tr></thead><tbody>
        @foreach($shipments as $shipment)<tr><td>{{ $shipment->tracking_code }}</td><td>{{ optional($shipment->trip)->reference ?: '—' }}</td><td>{{ optional($shipment->receipt_date)->format('Y-m-d') ?: $shipment->created_at->format('Y-m-d') }}</td><td>{{ $shipment->shipping_fee ?? '—' }}</td><td>{{ $shipment->paid_amount ?? '—' }}</td><td>{{ $shipment->balance ?? ($shipment->shipping_fee !== null && $shipment->paid_amount !== null ? bcsub($shipment->shipping_fee, $shipment->paid_amount, 2) : '—') }}</td><td class="account-no-print"><a href="{{ route('dashboard.landing.shipments.edit', $shipment) }}">{{ __('Edit') }}</a></td></tr>@endforeach
    </tbody></table></div></div>
    <div class="operations-pagination account-no-print">{{ $shipments->onEachSide(1)->links() }}</div>
    @if($history->isNotEmpty())<div class="card mt-4 account-no-print"><div class="card-body"><h2 class="h5">{{ __('Prepared messages for these amounts') }}</h2>@foreach($history as $statement)<p><a href="{{ route('dashboard.accounts.show', $statement->batch_id) }}">{{ $statement->created_at->format('Y-m-d H:i') }}</a> · {{ \App\Models\MerchantStatement::statusLabels()[$statement->status] ?? $statement->status }} · <span dir="ltr">{{ $statement->phone }}</span></p>@endforeach</div></div>@endif
</div></div></div>
@endsection
