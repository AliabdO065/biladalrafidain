<?php

namespace App\Http\Controllers;

use App\Jobs\SendMerchantStatement;
use App\Models\AccountBatch;
use App\Models\LandingTrip;
use App\Models\Merchant;
use App\Services\ShipmentAccounts;
use App\Services\TripAccountStatements;
use App\Services\WhatsAppAccounts;
use App\Support\ShipmentAccountIdentity;
use App\Support\DataTableResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MerchantAccountController extends Controller
{
    public function index(Request $request, ShipmentAccounts $accounts)
    {
        $filters = $accounts->filters($request);
        $merchants = $accounts->listing($filters);
        $trips = LandingTrip::latest('id')->get();

        return view('dashboard.accounts.index', compact('filters', 'merchants', 'trips'));
    }

    public function merchant(Request $request, string $key, ShipmentAccounts $accounts, WhatsAppAccounts $whatsapp)
    {
        $filters = $accounts->filters($request);
        $summary = $accounts->summary($key, $filters);
        $shipments = $accounts->accountQuery($key, $filters)->with('trip')->latest('id')->paginate(25)->withQueryString();
        $period = $accounts->period($filters);
        $configured = $whatsapp->configured();
        $trips = LandingTrip::latest('id')->get();
        $history = \App\Models\MerchantStatement::with('batch')->where('source_version', $summary['version'])->latest()->limit(5)->get();
        $accountContact = ($number = $shipments->first()?->merchant_number) ? \App\Models\TradingContact::where('number', $number)->first() : null;

        return view('dashboard.accounts.merchant', compact('key', 'filters', 'summary', 'shipments', 'period', 'configured', 'trips', 'history', 'accountContact'));
    }

    public function data(Request $request, ShipmentAccounts $accounts)
    {
        $filters = $accounts->filters(new Request($request->only(['trip_id', 'from', 'to'])));
        $query = $accounts->listingQuery($filters);

        return DataTableResponse::make($request, $query,
            ['contact.receiver_name', 'contact.receiver_phone', 'accounts.currency', 'accounts.shipment_count', 'accounts.shipping_fee', 'accounts.paid_amount', 'accounts.balance'],
            ['contact.receiver_name', 'contact.receiver_phone', 'contact.merchant_number', 'accounts.currency'], function ($merchant) use ($filters) {
                return [
                    'receiver_name' => $merchant->receiver_name ?: __('Unnamed merchant'), 'receiver_phone' => $merchant->receiver_phone,
                    'merchant_number' => $merchant->merchant_number, 'currency' => $merchant->currency ?: __('Unspecified currency'),
                    'shipment_count' => $merchant->shipment_count, 'shipping_fee' => $merchant->shipping_fee, 'paid_amount' => $merchant->paid_amount,
                    'balance' => $merchant->balance, 'incomplete' => (bool) $merchant->incomplete,
                    'actions' => view('dashboard.accounts._actions', compact('merchant', 'filters'))->render(),
                ];
            });
    }

    public function prepare(Request $request, string $key, ShipmentAccounts $accounts, WhatsAppAccounts $whatsapp)
    {
        $filters = $accounts->filters($request);
        $data = $request->validate([
            'version' => 'required|string|size:64', 'reviewed' => 'accepted',
            'phone' => 'required|string|max:50', 'country_code' => 'nullable|in:20,964',
            'currency' => 'required|in:EGP,IQD,USD,EUR,SAR,AED,TRY',
            'action' => 'required|in:manual,queue',
        ]);
        $phone = ShipmentAccountIdentity::phone($data['phone'], $data['country_code'] ?? null);
        if (! $phone) {
            throw ValidationException::withMessages(['phone' => __('Enter an international WhatsApp number or choose the country for a local number.')]);
        }
        if (! empty($filters['bucket']) && $data['currency'] !== $filters['bucket']) {
            throw ValidationException::withMessages(['currency' => __('The statement currency must match its shipments.')]);
        }
        if ($data['action'] === 'queue' && ! $whatsapp->configured()) {
            throw ValidationException::withMessages(['whatsapp' => __('Connect WhatsApp Business before bulk sending.')]);
        }
        $statement = DB::transaction(function () use ($accounts, $filters, $key, $data, $phone, $request) {
            $summary = $accounts->summary($key, $filters, true);
            if (! hash_equals($summary['version'], $data['version'])) {
                throw ValidationException::withMessages(['version' => __('Shipment amounts changed. Refresh and review the statement again.')]);
            }
            if ($summary['incomplete']) {
                throw ValidationException::withMessages(['amounts' => __('Complete missing shipping fees and collected amounts before sending.')]);
            }
            if (bccomp($summary['balance'], '9999999999999.99', 2) === 1 || bccomp($summary['balance'], '-9999999999999.99', 2) === -1) {
                throw ValidationException::withMessages(['amounts' => __('This balance exceeds the supported statement amount.')]);
            }
            $fingerprint = hash('sha256', json_encode([$key, $summary['version'], $phone, $data['currency'], $filters['trip_id'] ?? null, $filters['from'] ?? null, $filters['to'] ?? null]));
            $batch = AccountBatch::firstOrCreate(['file_hash' => $fingerprint], [
                'title' => mb_substr($accounts->period($filters), 0, 191), 'created_by' => $request->user()->id,
                'source' => 'shipments', 'source_filters' => array_merge($filters, ['account_key' => $key]),
            ]);
            $statement = $batch->statements()->first();
            if (! $statement) {
                $merchant = Merchant::firstOrCreate(['phone' => $phone], ['name' => $summary['name']]);
                $statement = $batch->statements()->create([
                    'merchant_id' => $merchant->id, 'name' => $summary['name'], 'phone' => $phone,
                    'amount' => $summary['balance'], 'currency' => $data['currency'],
                    'shipment_count' => $summary['count'], 'shipping_fee' => $summary['fee'], 'paid_amount' => $summary['paid'],
                    'source_version' => $summary['version'],
                    'details' => __('Shipping fees: :fee; collected: :paid; shipments: :count', ['fee' => $summary['fee'], 'paid' => $summary['paid'], 'count' => $summary['count']]),
                ]);
            }
            if ($data['action'] === 'queue') {
                $claimed = $batch->statements()->whereKey($statement->id)->where('status', 'pending')->update(['status' => 'queued']);
                if ($claimed) {
                    SendMerchantStatement::dispatch($statement->id);
                }
            }

            return $statement;
        });
        if ($data['action'] === 'manual') {
            return redirect()->away($statement->whatsappUrl());
        }

        return redirect()->route('dashboard.accounts.show', $statement->batch_id)->with('success', __('Statement prepared. Existing queued or accepted messages are not sent twice.'));
    }

    public function history()
    {
        $batches = AccountBatch::withCount('statements')->latest('id')->paginate(20);

        return view('dashboard.accounts.history', compact('batches'));
    }

    public function tripReview(LandingTrip $trip, ShipmentAccounts $accounts, WhatsAppAccounts $whatsapp)
    {
        $groups = $accounts->tripSummaries($trip->id);
        $version = $accounts->tripVersion($groups);
        $configured = $whatsapp->configured();

        return view('dashboard.accounts.trip', compact('trip', 'groups', 'version', 'configured'));
    }

    public function prepareTrip(Request $request, LandingTrip $trip, ShipmentAccounts $accounts, TripAccountStatements $preparer)
    {
        if (is_string($request->input('recipients'))) {
            $decoded = json_decode($request->input('recipients'), true);
            if (! is_array($decoded)) {
                throw ValidationException::withMessages(['recipients' => __('Review every merchant in this trip before preparing the batch.')]);
            }
            $request->merge(['recipients' => $decoded]);
        }
        $data = $request->validate([
            'version' => 'required|string|size:64', 'reviewed' => 'accepted',
            'default_country_code' => 'nullable|in:20,964', 'default_currency' => 'nullable|in:EGP,IQD,USD,EUR,SAR,AED,TRY',
            'recipients' => 'required|array|max:10000', 'recipients.*' => 'required|array',
            'recipients.*.phone' => 'required|string|max:50', 'recipients.*.country_code' => 'nullable|in:20,964',
            'recipients.*.currency' => 'nullable|in:EGP,IQD,USD,EUR,SAR,AED,TRY',
        ]);
        $batch = $preparer->prepare($trip, $data, $request->user()->id, $accounts);

        return redirect()->route('dashboard.accounts.show', $batch)->with('success', __('Trip accounts prepared. Each merchant has a separate message for this trip only.'));
    }

    public function show(AccountBatch $batch, WhatsAppAccounts $whatsapp)
    {
        $statements = $batch->statements()->with('batch')->paginate(50);
        $counts = $batch->statements()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $configured = $whatsapp->configured();

        return view('dashboard.accounts.show', compact('batch', 'statements', 'counts', 'configured'));
    }

    public function send(Request $request, AccountBatch $batch, WhatsAppAccounts $whatsapp, ShipmentAccounts $accounts, TripAccountStatements $preparer)
    {
        $request->validate(['reviewed' => 'accepted', 'retry_failed' => 'nullable|boolean']);
        if (! $whatsapp->configured()) {
            throw ValidationException::withMessages(['whatsapp' => __('Connect WhatsApp Business before bulk sending.')]);
        }
        $count = DB::transaction(function () use ($batch, $request, $accounts, $preparer) {
            if ($batch->source === 'trip_shipments') {
                LandingTrip::lockForUpdate()->findOrFail($batch->source_filters['trip_id']);
            }
            AccountBatch::lockForUpdate()->findOrFail($batch->id);
            $preparer->assertCurrent($batch, $accounts);
            $statuses = $request->boolean('retry_failed') ? ['failed'] : ['pending'];
            $statements = $batch->statements()->whereIn('status', $statuses)->lockForUpdate()->get();
            foreach ($statements as $statement) {
                if ($batch->source === 'trip_shipments' && \App\Models\MerchantStatement::where('source_version', $statement->source_version)
                    ->where('phone', $statement->phone)->where('currency', $statement->currency)->where('id', '!=', $statement->id)
                    ->whereIn('status', ['queued', 'processing', 'accepted', 'unknown'])->exists()) {
                    $statement->update(['status' => 'duplicate', 'last_error' => 'This account is already queued or handled in another message.']);
                    continue;
                }
                $statement->update(['status' => 'queued', 'last_error' => null]);
                SendMerchantStatement::dispatch($statement->id);
            }

            return $batch->statements()->whereIn('id', $statements->modelKeys())->where('status', 'queued')->count();
        });

        return back()->with('success', __(':count messages queued.', ['count' => $count]));
    }
}
