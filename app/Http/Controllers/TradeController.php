<?php

namespace App\Http\Controllers;

use App\Jobs\SendOperationNotification;
use App\Models\LandingShipment;
use App\Models\LandingTrip;
use App\Models\NotificationTemplate;
use App\Models\TradeImport;
use App\Models\TradeVoucher;
use App\Models\TradingContact;
use App\Models\WhatsappMessage;
use App\Services\OperationNotifications;
use App\Services\ShipmentDocument;
use App\Services\VoucherSpreadsheetImporter;
use App\Services\WhatsAppMessaging;
use App\Support\ShipmentAccountIdentity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;

class TradeController extends Controller
{
    public function prepareShipment(LandingShipment $shipment, OperationNotifications $notifications)
    {
        if (! $notifications->shipment($shipment)) {
            throw ValidationException::withMessages(['merchant_number' => __('flow.merchant_required')]);
        }

        return redirect()->route('dashboard.trade.messages', ['shipment_id' => $shipment->id])->with('success', __('flow.notification_prepared'));
    }

    public function prepareTrip(LandingTrip $trip, OperationNotifications $notifications)
    {
        $created = 0;
        $skipped = 0;
        foreach ($trip->shipments()->orderBy('id')->cursor() as $shipment) {
            $message = $notifications->shipment($shipment);
            if (! $message) {
                $skipped++;
            } elseif ($message->wasRecentlyCreated) {
                $created++;
            }
        }

        return redirect()->route('dashboard.trade.messages', ['trip_id' => $trip->id])->with('success', __('trade.trip_prepared', ['created' => $created, 'skipped' => $skipped]));
    }

    public function index()
    {
        $imports = TradeImport::latest()->paginate(15);
        $shipmentImports = \App\Models\ShipmentImport::latest()->limit(5)->get();

        return view('dashboard.trade.imports', compact('imports', 'shipmentImports'));
    }

    public function preview(Request $request, VoucherSpreadsheetImporter $importer)
    {
        $data = $request->validate(['file' => 'required|file|max:5120', 'source' => 'required|in:payments,receipts', 'currency' => 'required|in:EGP,IQD,USD,EUR,SAR,AED,TRY']);
        if (strtolower($request->file('file')->getClientOriginalExtension()) !== 'xlsx') {
            throw ValidationException::withMessages(['file' => __('Upload an XLSX receipt report.')]);
        }
        $import = $importer->preview($request->file('file'), $data['source'], $data['currency'], $request->user()->id);

        return redirect()->route('dashboard.trade.preview', $import);
    }

    public function review(TradeImport $import)
    {
        $rows = collect($import->payload ?? []);
        $old = TradeVoucher::where('source', $import->kind)->whereIn('reference', $rows->pluck('reference'))->pluck('fingerprint', 'reference');
        $conflicts = $rows->filter(fn ($row) => isset($old[$row['reference']]) && $old[$row['reference']] !== $row['fingerprint'])->count();
        $newCount = $rows->reject(fn ($row) => isset($old[$row['reference']]))->count();
        $page = max(1, (int) request('page', 1));
        $rows = new \Illuminate\Pagination\LengthAwarePaginator($rows->slice(($page - 1) * 50, 50)->values(), $rows->count(), 50, $page, ['path' => request()->url(), 'query' => request()->except('page')]);
        if ($import->confirmed_at) {
            // Confirmed financial rows remain available without retaining the uploaded file or preview payload.
            $rows = TradeVoucher::with('contact')->where('import_id', $import->id)->orderBy('id')->paginate(50)
                ->through(fn ($voucher) => array_merge($voucher->toArray(), ['name' => $voucher->contact->name, 'number' => $voucher->contact->number, 'occurred_at' => $voucher->occurred_at->format('Y-m-d')]));
        }

        return view('dashboard.trade.preview', compact('import', 'rows', 'conflicts', 'newCount'));
    }

    public function confirm(TradeImport $import, VoucherSpreadsheetImporter $importer)
    {
        $importer->confirm($import);

        return redirect()->route('dashboard.trade.preview', $import)->with('success', __('trade.imported'));
    }

    public function contacts(Request $request)
    {
        // Existing shipment data provides merchant identity; local numbers still require review.
        LandingShipment::whereNotNull('merchant_number')->whereNotIn('merchant_number', TradingContact::select('number'))->orderBy('id')->each(fn ($shipment) => TradingContact::fromShipment($shipment), 200);
        $filters = $request->validate(['search' => 'nullable|string|max:100', 'attention' => 'nullable|in:phone']);
        $search = $filters['search'] ?? '';
        $contacts = TradingContact::when(($filters['attention'] ?? '') === 'phone', fn ($q) => $q->where(fn ($s) => $s->whereNull('phone')->orWhere('phone', '')->orWhere('phone_conflict', true)->orWhere('opt_in', false)))
            ->when($search, fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', '%'.$search.'%')->orWhere('number', 'like', '%'.$search.'%')->orWhere('phone', 'like', '%'.$search.'%')))->orderBy('name')->paginate(20)->withQueryString();
        $suggestedPhones = LandingShipment::whereIn('merchant_number', $contacts->pluck('number'))->whereNotNull('receiver_phone')->orderBy('id')->pluck('receiver_phone', 'merchant_number');

        return view('dashboard.trade.contacts', compact('contacts', 'search', 'suggestedPhones'));
    }

    public function saveContact(Request $request, TradingContact $contact)
    {
        $data = $request->validate(['name' => 'required|string|max:191', 'phone' => 'nullable|string|max:30', 'country' => 'nullable|in:20,964', 'opt_in' => 'sometimes|accepted']);
        $phone = filled($data['phone'] ?? null) ? ShipmentAccountIdentity::phone($data['phone'], $data['country'] ?? null) : null;
        if (filled($data['phone'] ?? null) && ! $phone) {
            throw ValidationException::withMessages(['phone' => __('trade.review_phone')]);
        }
        $contact->update(['name' => $data['name'], 'phone' => $phone, 'opt_in' => $request->boolean('opt_in'), 'phone_conflict' => false]);

        return back()->with('success', __('trade.saved'));
    }

    public function movements(TradingContact $contact)
    {
        $vouchers = TradeVoucher::where('contact_id', $contact->id)->latest('occurred_at')->orderByDesc('id')->paginate(25);
        $totals = TradeVoucher::where('contact_id', $contact->id)->selectRaw('currency, kind, SUM(amount) AS amount')->groupBy('currency', 'kind')->get();

        $shipmentAccount = LandingShipment::where('merchant_number', $contact->number)->whereNotNull('account_key')->latest('id')->first();
        return view('dashboard.trade.movements', compact('contact', 'vouchers', 'totals', 'shipmentAccount'));
    }

    public function templates()
    {
        $templates = collect(array_keys(NotificationTemplate::FIELDS))->map(fn ($kind) => NotificationTemplate::forKind($kind));

        return view('dashboard.trade.templates', compact('templates'));
    }

    public function saveTemplate(Request $request, NotificationTemplate $template)
    {
        $data = $request->validate(['body' => 'required|string|max:3000', 'provider_name' => 'nullable|regex:/^[a-z0-9_]{1,100}$/D', 'language' => 'required|in:ar,en,en_US,en_GB', 'auto_send' => 'sometimes|accepted']);
        preg_match_all('/\{([^{}]+)\}/', $data['body'], $matches);
        if (array_diff($matches[1], NotificationTemplate::FIELDS[$template->kind])) {
            throw ValidationException::withMessages(['body' => __('trade.invalid_fields')]);
        }
        $body = str_replace(["\r\n", "\r"], "\n", $data['body']);
        $template->fill(['body' => $body, 'provider_name' => $data['provider_name'] ?? null, 'language' => $data['language']]);
        $changed = $template->isDirty(['body', 'provider_name', 'language']);
        if ($request->boolean('auto_send') && ! $changed && ! $template->isVerified()) {
            throw ValidationException::withMessages(['template' => __('trade.verify_template')]);
        }
        $template->auto_send = ! $changed && $request->boolean('auto_send');
        if ($changed) {
            $template->verified_hash = null;
        }
        $template->save();

        return back()->with('success', __('trade.saved'));
    }

    public function verifyTemplate(NotificationTemplate $template, WhatsAppMessaging $service)
    {
        $service->verify($template);

        return back()->with('success', __('trade.verified'));
    }

    public function messages(Request $request, WhatsAppMessaging $service)
    {
        $messageFilters = $request->validate([
            'phone' => 'nullable|string|max:30',
            'status' => 'nullable|in:draft,queued,processing,accepted,sent,delivered,read,received,failed,unknown,cancelled',
            'trip_id' => 'nullable|integer|exists:landing_trips,id',
            'contact_id' => 'nullable|integer|exists:trading_contacts,id',
            'import_id' => 'nullable|integer|exists:trade_imports,id',
            'shipment_import_id' => 'nullable|integer|exists:shipment_imports,id',
            'shipment_id' => 'nullable|integer|exists:landing_shipments,id',
        ]);
        $phone = (string) ($messageFilters['phone'] ?? '');
        $status = (string) ($messageFilters['status'] ?? '');
        $messages = WhatsappMessage::with(['contact', 'shipmentSource.trip', 'voucherSource.contact'])->operationFilters($messageFilters)
            ->when($phone, fn ($q) => $q->where('phone', $phone))->when($status, fn ($q) => $q->where('status', $status))->latest('id')->paginate(25)->withQueryString();
        $canReply = $phone && $service->canReply($phone);
        $contextTrip = ! empty($messageFilters['trip_id']) ? LandingTrip::find($messageFilters['trip_id']) : null;

        return view('dashboard.trade.messages', compact('messages', 'phone', 'status', 'canReply', 'messageFilters', 'contextTrip'));
    }

    public function send(WhatsappMessage $message, OperationNotifications $notifications)
    {
        abort_unless($message->direction === 'out' && $message->kind !== 'reply', 422);
        $notifications->queue($message);

        return back()->with('success', __('trade.queued'));
    }

    public function cancel(WhatsappMessage $message)
    {
        WhatsappMessage::whereKey($message->id)->where('direction', 'out')->whereIn('status', ['draft', 'queued', 'failed'])->update(['status' => 'cancelled']);

        return back()->with('success', __('trade.saved'));
    }

    public function refresh(WhatsappMessage $message, OperationNotifications $notifications)
    {
        $notifications->refresh($message);

        return back()->with('success', __('trade.saved'));
    }

    public function reply(Request $request, WhatsAppMessaging $service)
    {
        $data = $request->validate(['phone' => 'required|string|max:20', 'body' => 'required|string|max:4000', 'nonce' => 'required|uuid']);
        $phone = ShipmentAccountIdentity::phone($data['phone']);
        if (! $phone || ! $service->configured() || ! $service->canReply($phone)) {
            throw ValidationException::withMessages(['reply' => __('trade.window_closed')]);
        }
        $message = WhatsappMessage::firstOrCreate(['dedupe_key' => hash('sha256', 'reply:'.$request->user()->id.':'.$data['nonce'])], ['phone' => $phone, 'direction' => 'out', 'kind' => 'reply', 'body' => $data['body'], 'status' => 'queued']);
        if ($message->wasRecentlyCreated) {
            SendOperationNotification::dispatch($message->id)->onConnection('database')->onQueue('whatsapp');
        }

        return back()->with('success', __('trade.queued'));
    }

    public function document(LandingShipment $shipment)
    {
        return response()->view('dashboard.documents.shipment', compact('shipment'))->header('Cache-Control', 'private, no-store');
    }

    public function image(LandingShipment $shipment, ShipmentDocument $renderer)
    {
        if (! $renderer->binary()) {
            throw ValidationException::withMessages(['document' => __('trade.no_renderer')]);
        }
        try {
            $document = $renderer->render($shipment);
        } catch (\Throwable $error) {
            throw ValidationException::withMessages(['document' => __('trade.no_renderer')]);
        }
        try {
            $bytes = File::get($document['path']);
        } finally {
            File::deleteDirectory($document['folder']);
        }

        return response($bytes)->header('Content-Type', 'image/png')->header('Content-Disposition', 'attachment; filename="shipment-'.$shipment->id.'.png"')->header('Cache-Control', 'private, no-store');
    }
}
