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
use App\Models\LandingStat;
use App\Models\LandingStep;
use App\Models\Language;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        $settings = LandingSetting::first();
        $stats = LandingStat::orderBy('sort_order')->get();
        $services = LandingService::where('is_active', true)->orderBy('sort_order')->get();
        $steps = LandingStep::orderBy('sort_order')->get();
        $comparisons = LandingComparison::orderBy('sort_order')->get();
        $reviews = LandingReview::orderBy('sort_order')->get();
        $faqs = LandingFaq::orderBy('sort_order')->get();

        return view('fronted.landing.index', compact(
            'settings', 'stats', 'services', 'steps', 'comparisons', 'reviews', 'faqs'
        ));
    }

    public function submitCallback(Request $request)
    {
        $data = $request->validate([
            'problem_type' => 'required|string|in:shipping,customs_clearance,tracking_issue,other',
            'postal_code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'message' => 'nullable|string|max:2000',
        ]);

        LandingLead::create($data);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('fronted.index')->with('callback_success', true);
    }

    public function tracking(Request $request)
    {
        $settings = LandingSetting::first();
        $shipment = null;
        $notFound = false;

        $code = trim((string) $request->query('code', ''));

        if ($code !== '') {
            $shipment = LandingShipment::with('events')->where('tracking_code', $code)->first();
            $notFound = ! $shipment;
        }

        return view('fronted.landing.tracking', compact('settings', 'shipment', 'notFound', 'code'));
    }

    public function policies()
    {
        $settings = LandingSetting::first();
        $policies = LandingPolicy::where('is_active', true)->orderBy('sort_order')->get();

        return view('fronted.landing.policies', compact('settings', 'policies'));
    }

    public function setLocale(string $code)
    {
        if (Language::where('code', $code)->where('is_enabled', true)->exists()) {
            session(['site_locale' => $code]);
        }

        return redirect()->back();
    }
}
