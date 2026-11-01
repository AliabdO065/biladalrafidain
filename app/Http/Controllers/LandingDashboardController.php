<?php

namespace App\Http\Controllers;

use App\Models\LandingComparison;
use App\Models\LandingFaq;
use App\Models\LandingLead;
use App\Models\LandingPolicy;
use App\Models\LandingReview;
use App\Models\LandingService;
use App\Models\LandingSetting;
use App\Models\LandingShipment;
use App\Models\LandingShipmentEvent;
use App\Models\LandingTrip;
use App\Support\ShipmentStages;
use App\Support\ShipmentFields;
use App\Support\DataTableResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Models\LandingStat;
use App\Models\LandingStep;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LandingDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ----- Languages (enable/disable which locales are selectable on the site) -----

    public function languages()
    {
        $languages = Language::supported()->orderBy('sort_order')->get();

        return view('dashboard.landing.languages.index', compact('languages'));
    }

    public function updateLanguages(Request $request)
    {
        $request->validate(['enabled' => 'nullable|array']);
        $languages = Language::supported()->get();
        $enabledCodes = array_intersect(array_keys($request->input('enabled', []) ?? []), Language::SUPPORTED);
        Language::whereNotIn('code', Language::SUPPORTED)->update(['is_enabled' => false, 'is_default' => false]);

        // Always keep the default language enabled, and never allow every language to be disabled.
        $defaultCode = optional($languages->firstWhere('is_default', true))->code;
        if ($defaultCode && ! in_array($defaultCode, $enabledCodes, true)) {
            $enabledCodes[] = $defaultCode;
        }
        if (empty($enabledCodes) && $languages->isNotEmpty()) {
            $enabledCodes = [$languages->first()->code];
        }

        foreach ($languages as $language) {
            $language->update(['is_enabled' => in_array($language->code, $enabledCodes, true)]);
        }

        return redirect()->route('dashboard.landing.languages')->with('success', __('Sprachen aktualisiert.'));
    }

    /**
     * Move an uploaded file into public/images/landing/<folder>, delete the previous
     * file at $oldPath if one existed, and return the new relative path (or $oldPath
     * unchanged if no new file was uploaded).
     */
    protected function handleUpload(Request $request, string $fileField, ?string $oldPath, string $folder): ?string
    {
        if (! $request->hasFile($fileField)) {
            return $oldPath;
        }

        $destination = public_path('images/landing/' . $folder);
        if (! File::exists($destination)) {
            File::makeDirectory($destination, 0755, true, true);
        }

        $file = $request->file($fileField);
        $filename = Str::uuid() . '-' . $file->getClientOriginalName();
        $file->move($destination, $filename);

        if ($oldPath && File::exists(public_path($oldPath))) {
            File::delete(public_path($oldPath));
        }

        return 'images/landing/' . $folder . '/' . $filename;
    }

    // ----- Settings (singleton) -----

    public function settings()
    {
        $settings = LandingSetting::firstOrCreate([]);

        return view('dashboard.landing.settings.index', compact('settings'));
    }

    public function about()
    {
        $settings = LandingSetting::firstOrCreate([]);

        return view('dashboard.landing.about.index', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        // These fields only live on the main Settings form; other pages (Services,
        // Steps, ...) post just their own section-heading fields via this same
        // route, so only validate ones actually present in the request.
        $request->validate([
            'phone_display' => 'sometimes|required|string|max:255',
            'phone_href' => 'sometimes|required|string|max:255',
            'hero_headline.ar' => 'sometimes|required|string|max:255',
            'company_name.ar' => 'sometimes|required|string|max:255',
        ]);

        $settings = LandingSetting::firstOrCreate([]);

        $data = $request->except(['_token', 'logo_image_file', 'hero_image_file', 'about_owner_photo_file']);

        // handleUpload() is self-guarding (it only acts when a file was actually
        // sent), so these can run unconditionally regardless of which page's
        // mini-form submitted this request.
        $data['logo_image'] = $this->handleUpload($request, 'logo_image_file', $settings->logo_image, 'settings');
        $data['hero_image'] = $this->handleUpload($request, 'hero_image_file', $settings->hero_image, 'settings');
        $data['about_owner_photo'] = $this->handleUpload($request, 'about_owner_photo_file', $settings->about_owner_photo, 'settings');

        // Checkboxes are ambiguous when absent (unchecked vs. "this form doesn't
        // render this field at all"), so only the main Settings page's own
        // checkboxes get touched, guarded by a field only that page submits.
        if ($request->has('phone_display')) {
            $data['alert_banner_active'] = $request->has('alert_banner_active');
            $data['navbar_show_brand_text'] = $request->has('navbar_show_brand_text');
            foreach (['nav_show_services', 'nav_show_steps', 'nav_show_about', 'nav_show_comparison', 'nav_show_reviews', 'nav_show_faq', 'nav_show_callback', 'nav_show_tracking', 'nav_show_policies'] as $navField) {
                $data[$navField] = $request->has($navField);
            }
        }

        $settings->update($data);

        return redirect()->back()->with('success', __('Einstellungen gespeichert.'));
    }

    // ----- Stats (repeatable, no images) -----

    public function stats()
    {
        $items = LandingStat::orderBy('sort_order')->get();
        $settings = LandingSetting::firstOrCreate([]);

        return view('dashboard.landing.stats.all', compact('items', 'settings'));
    }

    public function addStat()
    {
        return view('dashboard.landing.stats.add');
    }

    public function storeStat(Request $request)
    {
        $data = $request->validate([
            'value' => 'required|string|max:255',
            'label.ar' => 'required|string|max:255',
            'label.en' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        LandingStat::create($data);

        return redirect()->route('dashboard.landing.stats')->with('success', __('Eintrag hinzugefügt.'));
    }

    public function editStat($id)
    {
        $item = LandingStat::findOrFail($id);

        return view('dashboard.landing.stats.edit', compact('item'));
    }

    public function updateStat(Request $request, $id)
    {
        $item = LandingStat::findOrFail($id);
        $data = $request->validate([
            'value' => 'required|string|max:255',
            'label.ar' => 'required|string|max:255',
            'label.en' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);
        $item->update($data);

        return redirect()->route('dashboard.landing.stats')->with('success', __('Eintrag aktualisiert.'));
    }

    public function deleteStat($id)
    {
        LandingStat::findOrFail($id)->delete();

        return redirect()->route('dashboard.landing.stats')->with('success', __('Eintrag gelöscht.'));
    }

    // ----- Services (repeatable, with image) -----

    public function services()
    {
        $items = LandingService::orderBy('sort_order')->get();
        $settings = LandingSetting::firstOrCreate([]);

        return view('dashboard.landing.services.all', compact('items', 'settings'));
    }

    public function addService()
    {
        return view('dashboard.landing.services.add');
    }

    public function storeService(Request $request)
    {
        $data = $request->validate([
            'title.ar' => 'required|string|max:255',
            'title.en' => 'nullable|string|max:255',
            'description.ar' => 'nullable|string',
            'description.en' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);
        $data['is_active'] = $request->has('is_active');
        $data['image'] = $this->handleUpload($request, 'image', null, 'services');

        LandingService::create($data);

        return redirect()->route('dashboard.landing.services')->with('success', __('Service hinzugefügt.'));
    }

    public function editService($id)
    {
        $item = LandingService::findOrFail($id);

        return view('dashboard.landing.services.edit', compact('item'));
    }

    public function updateService(Request $request, $id)
    {
        $item = LandingService::findOrFail($id);
        $data = $request->validate([
            'title.ar' => 'required|string|max:255',
            'title.en' => 'nullable|string|max:255',
            'description.ar' => 'nullable|string',
            'description.en' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);
        $data['is_active'] = $request->has('is_active');
        $data['image'] = $this->handleUpload($request, 'image', $item->image, 'services');
        $item->update($data);

        return redirect()->route('dashboard.landing.services')->with('success', __('Service aktualisiert.'));
    }

    public function deleteService($id)
    {
        $item = LandingService::findOrFail($id);
        if ($item->image && File::exists(public_path($item->image))) {
            File::delete(public_path($item->image));
        }
        $item->delete();

        return redirect()->route('dashboard.landing.services')->with('success', __('Service gelöscht.'));
    }

    // ----- Steps (repeatable, with image) -----

    public function steps()
    {
        $items = LandingStep::orderBy('sort_order')->get();
        $settings = LandingSetting::firstOrCreate([]);

        return view('dashboard.landing.steps.all', compact('items', 'settings'));
    }

    public function addStep()
    {
        return view('dashboard.landing.steps.add');
    }

    public function storeStep(Request $request)
    {
        $data = $request->validate([
            'step_number' => 'required|integer|min:1|max:20',
            'title.ar' => 'required|string|max:255',
            'title.en' => 'nullable|string|max:255',
            'description.ar' => 'nullable|string',
            'description.en' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);
        $data['image'] = $this->handleUpload($request, 'image', null, 'steps');

        LandingStep::create($data);

        return redirect()->route('dashboard.landing.steps')->with('success', __('Schritt hinzugefügt.'));
    }

    public function editStep($id)
    {
        $item = LandingStep::findOrFail($id);

        return view('dashboard.landing.steps.edit', compact('item'));
    }

    public function updateStep(Request $request, $id)
    {
        $item = LandingStep::findOrFail($id);
        $data = $request->validate([
            'step_number' => 'required|integer|min:1|max:20',
            'title.ar' => 'required|string|max:255',
            'title.en' => 'nullable|string|max:255',
            'description.ar' => 'nullable|string',
            'description.en' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);
        $data['image'] = $this->handleUpload($request, 'image', $item->image, 'steps');
        $item->update($data);

        return redirect()->route('dashboard.landing.steps')->with('success', __('Schritt aktualisiert.'));
    }

    public function deleteStep($id)
    {
        $item = LandingStep::findOrFail($id);
        if ($item->image && File::exists(public_path($item->image))) {
            File::delete(public_path($item->image));
        }
        $item->delete();

        return redirect()->route('dashboard.landing.steps')->with('success', __('Schritt gelöscht.'));
    }

    // ----- Comparisons (repeatable, no images) -----

    public function comparisons()
    {
        $items = LandingComparison::orderBy('sort_order')->get();
        $settings = LandingSetting::firstOrCreate([]);

        return view('dashboard.landing.comparisons.all', compact('items', 'settings'));
    }

    public function addComparison()
    {
        return view('dashboard.landing.comparisons.add');
    }

    public function storeComparison(Request $request)
    {
        $data = $request->validate([
            'criterion.ar' => 'required|string|max:255',
            'criterion.en' => 'nullable|string|max:255',
            'us_value.ar' => 'required|string|max:255',
            'us_value.en' => 'nullable|string|max:255',
            'them_value.ar' => 'required|string|max:255',
            'them_value.en' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);
        $data['us_is_positive'] = $request->has('us_is_positive');
        $data['them_is_positive'] = $request->has('them_is_positive');

        LandingComparison::create($data);

        return redirect()->route('dashboard.landing.comparisons')->with('success', __('Zeile hinzugefügt.'));
    }

    public function editComparison($id)
    {
        $item = LandingComparison::findOrFail($id);

        return view('dashboard.landing.comparisons.edit', compact('item'));
    }

    public function updateComparison(Request $request, $id)
    {
        $item = LandingComparison::findOrFail($id);
        $data = $request->validate([
            'criterion.ar' => 'required|string|max:255',
            'criterion.en' => 'nullable|string|max:255',
            'us_value.ar' => 'required|string|max:255',
            'us_value.en' => 'nullable|string|max:255',
            'them_value.ar' => 'required|string|max:255',
            'them_value.en' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);
        $data['us_is_positive'] = $request->has('us_is_positive');
        $data['them_is_positive'] = $request->has('them_is_positive');
        $item->update($data);

        return redirect()->route('dashboard.landing.comparisons')->with('success', __('Zeile aktualisiert.'));
    }

    public function deleteComparison($id)
    {
        LandingComparison::findOrFail($id)->delete();

        return redirect()->route('dashboard.landing.comparisons')->with('success', __('Zeile gelöscht.'));
    }

    // ----- Reviews (repeatable, with image) -----

    public function reviews()
    {
        $items = LandingReview::orderBy('sort_order')->get();
        $settings = LandingSetting::firstOrCreate([]);

        return view('dashboard.landing.reviews.all', compact('items', 'settings'));
    }

    public function addReview()
    {
        return view('dashboard.landing.reviews.add');
    }

    public function storeReview(Request $request)
    {
        $data = $request->validate([
            'author_name' => 'required|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'review_date' => 'nullable|date',
            'review_text.ar' => 'required|string',
            'review_text.en' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);
        $data['is_placeholder'] = $request->has('is_placeholder');
        $data['author_photo'] = $this->handleUpload($request, 'author_photo', null, 'reviews');

        LandingReview::create($data);

        return redirect()->route('dashboard.landing.reviews')->with('success', __('Bewertung hinzugefügt.'));
    }

    public function editReview($id)
    {
        $item = LandingReview::findOrFail($id);

        return view('dashboard.landing.reviews.edit', compact('item'));
    }

    public function updateReview(Request $request, $id)
    {
        $item = LandingReview::findOrFail($id);
        $data = $request->validate([
            'author_name' => 'required|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'review_date' => 'nullable|date',
            'review_text.ar' => 'required|string',
            'review_text.en' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);
        $data['is_placeholder'] = $request->has('is_placeholder');
        $data['author_photo'] = $this->handleUpload($request, 'author_photo', $item->author_photo, 'reviews');
        $item->update($data);

        return redirect()->route('dashboard.landing.reviews')->with('success', __('Bewertung aktualisiert.'));
    }

    public function deleteReview($id)
    {
        $item = LandingReview::findOrFail($id);
        if ($item->author_photo && File::exists(public_path($item->author_photo))) {
            File::delete(public_path($item->author_photo));
        }
        $item->delete();

        return redirect()->route('dashboard.landing.reviews')->with('success', __('Bewertung gelöscht.'));
    }

    // ----- FAQs (repeatable, no images) -----

    public function faqs()
    {
        $items = LandingFaq::orderBy('sort_order')->get();
        $settings = LandingSetting::firstOrCreate([]);

        return view('dashboard.landing.faqs.all', compact('items', 'settings'));
    }

    public function addFaq()
    {
        return view('dashboard.landing.faqs.add');
    }

    public function storeFaq(Request $request)
    {
        $data = $request->validate([
            'question.ar' => 'required|string|max:255',
            'question.en' => 'nullable|string|max:255',
            'answer.ar' => 'required|string',
            'answer.en' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        LandingFaq::create($data);

        return redirect()->route('dashboard.landing.faqs')->with('success', __('Frage hinzugefügt.'));
    }

    public function editFaq($id)
    {
        $item = LandingFaq::findOrFail($id);

        return view('dashboard.landing.faqs.edit', compact('item'));
    }

    public function updateFaq(Request $request, $id)
    {
        $item = LandingFaq::findOrFail($id);
        $data = $request->validate([
            'question.ar' => 'required|string|max:255',
            'question.en' => 'nullable|string|max:255',
            'answer.ar' => 'required|string',
            'answer.en' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);
        $item->update($data);

        return redirect()->route('dashboard.landing.faqs')->with('success', __('Frage aktualisiert.'));
    }

    public function deleteFaq($id)
    {
        LandingFaq::findOrFail($id)->delete();

        return redirect()->route('dashboard.landing.faqs')->with('success', __('Frage gelöscht.'));
    }

    // ----- Leads (admin-view-only) -----

    public function leads()
    {
        $items = LandingLead::latest()->paginate(20);
        $settings = LandingSetting::firstOrCreate([]);

        return view('dashboard.landing.leads.index', compact('items', 'settings'));
    }

    public function deleteLead($id)
    {
        LandingLead::findOrFail($id)->delete();

        return redirect()->route('dashboard.landing.leads')->with('success', __('Anfrage gelöscht.'));
    }

    // ----- Policies (repeatable, no images) -----

    public function policies()
    {
        $items = LandingPolicy::orderBy('sort_order')->get();

        return view('dashboard.landing.policies.all', compact('items'));
    }

    public function addPolicy()
    {
        return view('dashboard.landing.policies.add');
    }

    public function storePolicy(Request $request)
    {
        $data = $request->validate([
            'slug' => 'required|string|max:255|alpha_dash|unique:landing_policies,slug',
            'title.ar' => 'required|string|max:255',
            'title.en' => 'nullable|string|max:255',
            'body.ar' => 'nullable|string',
            'body.en' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);
        $data['is_active'] = $request->has('is_active');

        LandingPolicy::create($data);

        return redirect()->route('dashboard.landing.policies')->with('success', __('Policy added.'));
    }

    public function editPolicy($id)
    {
        $item = LandingPolicy::findOrFail($id);

        return view('dashboard.landing.policies.edit', compact('item'));
    }

    public function updatePolicy(Request $request, $id)
    {
        $item = LandingPolicy::findOrFail($id);
        $data = $request->validate([
            'slug' => 'required|string|max:255|alpha_dash|unique:landing_policies,slug,' . $item->id,
            'title.ar' => 'required|string|max:255',
            'title.en' => 'nullable|string|max:255',
            'body.ar' => 'nullable|string',
            'body.en' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);
        $data['is_active'] = $request->has('is_active');
        $item->update($data);

        return redirect()->route('dashboard.landing.policies')->with('success', __('Policy updated.'));
    }

    public function deletePolicy($id)
    {
        LandingPolicy::findOrFail($id)->delete();

        return redirect()->route('dashboard.landing.policies')->with('success', __('Policy deleted.'));
    }

    // ----- Shipments (tracking) -----

    public function shipments(Request $request)
    {
        $shipmentFilters = $request->validate(['trip_id' => 'nullable|integer|exists:landing_trips,id', 'import_id' => 'nullable|integer|exists:shipment_imports,id', 'attention' => 'nullable|in:financial']);
        $trips = LandingTrip::latest()->get();

        return view('dashboard.landing.shipments.all', compact('trips', 'shipmentFilters'));
    }

    public function shipmentsData(Request $request)
    {
        $request->validate(['trip_id' => 'nullable|integer|exists:landing_trips,id', 'import_id' => 'nullable|integer|exists:shipment_imports,id', 'attention' => 'nullable|in:financial']);
        $query = LandingShipment::select('landing_shipments.*')->with('trip')
            ->leftJoin('landing_trips', 'landing_trips.id', '=', 'landing_shipments.trip_id');
        if ($request->filled('trip_id')) {
            $query->where('landing_shipments.trip_id', $request->input('trip_id'));
        }
        if ($request->filled('import_id')) {
            $query->where('landing_shipments.import_id', $request->input('import_id'));
        }
        if ($request->input('attention') === 'financial') {
            $query->where(fn ($q) => $q->whereNull('shipping_fee')->orWhereNull('paid_amount')->orWhereNull('currency'));
        }
        $detailFields = array_keys(ShipmentFields::labels());
        $columns = array_merge(['landing_shipments.id', 'landing_shipments.tracking_code', 'landing_trips.reference'],
            array_map(fn ($field) => 'landing_shipments.' . $field, $detailFields),
            ['landing_shipments.origin', 'landing_shipments.destination', 'landing_shipments.estimated_delivery',
                DB::raw('COALESCE(landing_trips.status, landing_shipments.status)'), 'landing_shipments.created_at']);
        $searchable = array_merge(['landing_shipments.tracking_code', 'landing_trips.reference'],
            array_map(fn ($field) => 'landing_shipments.' . $field, $detailFields), ['landing_shipments.origin', 'landing_shipments.destination']);

        return DataTableResponse::make($request, $query, $columns, $searchable, function ($item) use ($detailFields) {
            $row = $item->only($detailFields);
            $row['receipt_date'] = optional($item->receipt_date)->format('Y-m-d');

            return array_merge($row, [
                'id' => $item->id, 'tracking_code' => $item->tracking_code, 'trip_reference' => optional($item->trip)->reference,
                'origin' => $item->trip ? $item->trip->origin : $item->origin,
                'destination' => $item->city ?: ($item->trip ? $item->trip->destination : $item->destination),
                'estimated_delivery' => optional($item->trip ? $item->trip->estimated_delivery : $item->estimated_delivery)->format('Y-m-d'),
                'status' => ShipmentStages::label($item->current_status), 'created_at' => $item->created_at->format('Y-m-d H:i'),
                'status_code' => $item->current_status, 'trip_url' => $item->trip ? route('dashboard.trips.show', $item->trip) : null,
                'actions' => view('dashboard.landing.shipments._actions', compact('item'))->render(),
            ]);
        }, [['column' => DB::raw('COALESCE(landing_trips.status, landing_shipments.status)'), 'labels' => ShipmentStages::labels()]]);
    }

    public function addShipment()
    {
        return redirect()->route('dashboard.landing.shipments', ['create' => 1, 'trip_id' => request('trip_id')]);
    }

    public function storeShipment(Request $request)
    {
        $data = $request->validateWithBag('shipmentCreate', array_merge(ShipmentFields::rules(), [
            'tracking_code' => 'nullable|string|max:191|unique:landing_shipments,tracking_code',
            'trip_id' => 'nullable|exists:landing_trips,id',
            'sender_name' => 'nullable|string|max:255',
            'receiver_name' => 'nullable|string|max:255',
            'origin' => 'nullable|string|max:255',
            'destination' => 'nullable|string|max:255',
            'status' => ['required', Rule::in(array_keys(ShipmentStages::labels()))],
            'estimated_delivery' => 'nullable|date',
        ]));

        $data['tracking_code'] = $data['tracking_code'] ?? 'BR-' . strtoupper(Str::random(16));
        $shipment = DB::transaction(function () use ($data) {
            if (! empty($data['trip_id'])) {
                $trip = LandingTrip::lockForUpdate()->findOrFail($data['trip_id']);
                $data['origin'] = $trip->origin;
                $data['destination'] = $data['city'] ?? $trip->destination;
                $data['status'] = $trip->status;
            }
            $shipment = LandingShipment::create($data);
            $shipment->events()->create(['status' => $data['status'], 'happened_at' => now()]);
            return $shipment;
        });

        return redirect()->route('dashboard.landing.shipments', array_filter(['trip_id' => $shipment->trip_id]))->with('success', __('Shipment added.'));
    }

    public function editShipment($id)
    {
        $item = LandingShipment::with('events', 'trip')->findOrFail($id);
        $trips = LandingTrip::latest()->get();
        $contact = filled($item->merchant_number) ? \App\Models\TradingContact::where('number', $item->merchant_number)->first() : null;

        return view('dashboard.landing.shipments.edit', compact('item', 'trips', 'contact'));
    }

    public function updateShipment(Request $request, $id)
    {
        $item = LandingShipment::findOrFail($id);
        $data = $request->validate(array_merge(ShipmentFields::rules(), [
            'tracking_code' => 'required|string|max:191|unique:landing_shipments,tracking_code,' . $item->id,
            'trip_id' => 'nullable|exists:landing_trips,id',
            'sender_name' => 'nullable|string|max:255',
            'receiver_name' => 'nullable|string|max:255',
            'origin' => 'nullable|string|max:255',
            'destination' => 'nullable|string|max:255',
            'status' => ['required', Rule::in(array_merge(array_keys(ShipmentStages::labels()), ['on_hold']))],
            'estimated_delivery' => 'nullable|date',
        ]));
        unset($data['status']);
        $data['trip_id'] = array_key_exists('trip_id', $data) ? $data['trip_id'] : $item->trip_id;
        DB::transaction(function () use ($item, $data) {
            // Lock trips before their shipment, matching trip statement preparation.
            $tripIds = array_unique(array_filter([$item->trip_id, $data['trip_id'] ?? null]));
            $lockedTrips = LandingTrip::whereIn('id', $tripIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $locked = LandingShipment::lockForUpdate()->findOrFail($item->id);
            if ((int) $locked->trip_id !== (int) $item->trip_id) {
                throw ValidationException::withMessages(['trip_id' => __('flow.shipment_changed')]);
            }
            $oldStatus = $locked->trip_id ? $lockedTrips->get($locked->trip_id)->status : $locked->status;
            if (! empty($data['trip_id'])) {
                $trip = $lockedTrips->get($data['trip_id']);
                $data['origin'] = $trip->origin;
                $data['destination'] = $data['city'] ?? $trip->destination;
                $data['status'] = $trip->status;
            } elseif ($locked->trip_id) {
                // Detaching retains the actual current stage instead of resurrecting the old standalone status.
                $data['status'] = $oldStatus;
                $data['origin'] = $lockedTrips->get($locked->trip_id)->origin;
                $data['destination'] = $data['city'] ?? $lockedTrips->get($locked->trip_id)->destination;
            }
            $locked->update($data);
            if (($oldStatus !== $locked->status && ! empty($data['trip_id'])) || ($item->trip_id && empty($data['trip_id']))) {
                $locked->events()->create(['status' => $locked->status, 'happened_at' => now()]);
            }
        });

        return redirect()->route('dashboard.landing.shipments.edit', $item->id)->with('success', __('Shipment updated.'));
    }

    public function deleteShipment($id)
    {
        LandingShipment::findOrFail($id)->delete();

        return redirect()->route('dashboard.landing.shipments')->with('success', __('Shipment deleted.'));
    }

    public function advanceShipment(Request $request, $id)
    {
        $request->validate(['expected_status' => 'required|string']);
        DB::transaction(function () use ($request, $id) {
            $shipment = LandingShipment::lockForUpdate()->findOrFail($id);
            $next = ShipmentStages::next($shipment->status);
            if ($shipment->trip_id || ! $next || $shipment->status !== $request->input('expected_status')) {
                throw ValidationException::withMessages(['status' => __('Cannot advance this shipment. Refresh the page or update its trip.')]);
            }
            $shipment->update(['status' => $next]);
            $shipment->events()->create(['status' => $next, 'happened_at' => now()]);
        });

        return back()->with('success', __('Shipment updated.'));
    }

    public function storeShipmentEvent(Request $request, $id)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(ShipmentStages::labels()))],
            'note.ar' => 'nullable|string|max:255',
            'note.en' => 'nullable|string|max:255',
            'happened_at' => 'required|date',
        ]);
        $shipment = DB::transaction(function () use ($id, $data) {
            $shipment = LandingShipment::lockForUpdate()->findOrFail($id);
            if ($shipment->trip_id || ShipmentStages::next($shipment->status) !== $data['status']) {
                throw ValidationException::withMessages(['status' => __('Cannot advance this shipment. Refresh the page or update its trip.')]);
            }
            $shipment->events()->create($data);
            $shipment->update(['status' => $data['status']]);
            return $shipment;
        });

        return redirect()->route('dashboard.landing.shipments.edit', $shipment->id)->with('success', __('Event added.'));
    }

    public function deleteShipmentEvent($id, $eventId)
    {
        DB::transaction(function () use ($id, $eventId) {
            $shipment = LandingShipment::lockForUpdate()->findOrFail($id);
            $event = $shipment->events()->findOrFail($eventId);
            if ($shipment->trip_id || $shipment->events()->reorder()->latest('id')->value('id') === $event->id) {
                throw ValidationException::withMessages(['status' => __('flow.history_required')]);
            }
            $event->delete();
        });

        return redirect()->route('dashboard.landing.shipments.edit', $id)->with('success', __('Event deleted.'));
    }
}
