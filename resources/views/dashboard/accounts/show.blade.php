@extends('dashboard.layouts.layout')
@section('content')
<div class="wrapper"><div class="page"><div class="page-inner">
    <a href="{{ route('dashboard.accounts.index') }}">{{ __('Merchant Accounts') }}</a>
    <h1 class="h3 my-3">{{ $batch->title }}</h1>
    @if($batch->source === 'trip_shipments')<div class="alert alert-info">{{ __('This batch contains only this trip accounts. Every merchant receives only their own statement.') }} <a href="{{ route('dashboard.accounts.trip', $batch->source_filters['trip_id']) }}">{{ __('Review trip accounts again') }}</a></div>@endif
    <p class="text-muted">{{ __('This is a saved message summary. Current balances are available on Merchant Accounts.') }} <a href="{{ route('dashboard.accounts.history') }}">{{ __('Statement history') }}</a></p>
    @include('dashboard.partials.feedback')
    @include('dashboard.accounts._connection')
    <div class="card mb-4"><div class="card-body">
        <div class="mb-3">@foreach(\App\Models\MerchantStatement::statusLabels() as $key => $label)<span class="badge badge-light p-2 m-1">{{ $label }}: {{ $counts[$key] ?? 0 }}</span>@endforeach</div>
        <p>{{ __('Each merchant receives only their own account. Review names, phone numbers, amounts and currencies before sending.') }}</p>
        {{-- Automatic sending retained for a future phase. Manual WhatsApp is the current workflow.
        <form action="{{ route('dashboard.accounts.send', $batch) }}" method="POST" data-once>
            @csrf
            <div class="mb-3"><label><input type="checkbox" name="reviewed" value="1" required @disabled(!$configured)> {{ __('I reviewed this account batch and its recipients.') }}</label></div>
            <button class="btn btn-primary" @disabled(!$configured || !($counts['pending'] ?? 0))>{{ $batch->source === 'trip_shipments' ? __('Send all pending trip accounts') : __('Send pending accounts via WhatsApp') }}</button>
        </form>
        @if($counts['failed'] ?? 0)
        <form action="{{ route('dashboard.accounts.send', $batch) }}" method="POST" class="mt-3" data-once>
            @csrf<input type="hidden" name="retry_failed" value="1">
            <label><input type="checkbox" name="reviewed" value="1" required @disabled(!$configured)> {{ __('I reviewed the failed messages before retrying.') }}</label>
            <button class="btn btn-outline-primary" @disabled(!$configured)>{{ __('Retry failed messages only') }}</button>
        </form>
        @endif
        <p class="text-muted mt-3 mb-0">{{ __('Accepted by WhatsApp means the API accepted the message, not that the merchant received or read it. Uncertain messages are not retried automatically.') }}</p>
        <a href="{{ route('dashboard.accounts.show', ['batch' => $batch->id, 'page' => request('page', 1)]) }}" class="btn btn-light mt-3">{{ __('Refresh statuses') }}</a>
        --}}
        <p>{{ __('manual.instructions') }}</p>
        <button type="button" class="btn btn-success" data-manual-next>{{ __('manual.next') }}</button>
        <p class="text-muted mt-3 mb-0">{{ __('manual.local_notice') }}</p>
        <p class="text-muted mb-0" data-manual-complete hidden>{{ __('manual.page_complete') }}</p>
    </div></div>
    <div class="card"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>{{ __('Merchant') }}</th><th>{{ __('Phone') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Details') }}</th><th>{{ __('Status') }}</th><th>{{ __('WhatsApp') }}</th></tr></thead><tbody>
        @foreach($statements as $statement)<tr data-manual-row="{{ $statement->id }}">
            <td>{{ $statement->name }}</td><td dir="ltr">{{ $statement->phone }}</td><td>{{ $statement->amount }} {{ $statement->currency }}</td><td>{{ $statement->details }}</td>
            <td>{{ \App\Models\MerchantStatement::statusLabels()[$statement->status] ?? $statement->status }}
                @if($statement->last_error)<small class="d-block text-danger">{{ $statement->error_message }}</small>@endif
                @if($statement->provider_message_id)<small class="d-block text-muted">{{ $statement->provider_message_id }}</small>@endif
            </td>
            <td>@if(in_array($statement->status, ['pending', 'failed'], true))<a class="btn btn-sm btn-outline-success" data-manual-open href="{{ $statement->whatsappUrl() }}" target="_blank" rel="noopener noreferrer" referrerpolicy="no-referrer">{{ __('Open message') }}</a><label class="d-flex align-items-center mt-2" style="gap: .5rem"><input type="checkbox" data-manual-done disabled> {{ __('manual.confirm') }}</label>@endif</td>
        </tr>@endforeach
    </tbody></table></div></div>
    <div class="operations-pagination">{{ $statements->onEachSide(1)->links() }}</div>
</div></div></div>
@endsection
@push('scripts')
<script src="{{ asset('dashboard/assets/javascript/manual-accounts.js') }}?v={{ filemtime(public_path('dashboard/assets/javascript/manual-accounts.js')) }}" data-manual-account-key="{{ auth()->id() }}:{{ $batch->id }}"></script>
@endpush
