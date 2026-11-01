@extends('dashboard.layouts.layout')
@section('content')
<div class="wrapper"><div class="page"><div class="page-inner operations-detail-page">
    <header class="operations-page-header"><div><h1 class="h3">{{ __('Statement history') }}</h1><p class="text-muted">{{ __('Saved sending summaries and historical statements. Current balances come from shipments.') }}</p></div><a class="btn btn-outline-primary" href="{{ route('dashboard.accounts.index') }}">{{ __('Merchant Accounts') }}</a></header>
    <div class="card"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>{{ __('Statement period / title') }}</th><th>{{ __('Date') }}</th><th>{{ __('Source') }}</th><th>{{ __('Merchants') }}</th><th></th></tr></thead><tbody>
        @forelse($batches as $batch)<tr><td>{{ $batch->title }}</td><td>{{ $batch->created_at->format('Y-m-d H:i') }}</td><td>{{ in_array($batch->source, ['shipments','trip_shipments'], true) ? __('Shipments') : __('Historical statement') }}</td><td>{{ $batch->statements_count }}</td><td><a href="{{ route('dashboard.accounts.show', $batch) }}">{{ __('Review accounts') }}</a></td></tr>@empty<tr><td colspan="5">{{ __('No prepared statements yet.') }}</td></tr>@endforelse
    </tbody></table></div></div><div class="operations-pagination">{{ $batches->onEachSide(1)->links() }}</div>
</div></div></div>
@endsection
