<?php

namespace App\Http\Controllers;

use App\Models\LandingLead;
use App\Models\LandingTrip;
use App\Models\LandingShipment;
use App\Models\Merchant;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function index()
    {
        $tripsCount = LandingTrip::where('status', '!=', 'delivered')->count();
        $shipmentsCount = LandingShipment::count();
        // Phase 3 deferred: $merchantsCount = LandingShipment::whereNotNull('account_key')->distinct()->count('account_key');
        $merchantsCount = 0;
        $leadsCount = LandingLead::count();
        $attention = [
            'financial' => LandingShipment::where(fn ($q) => $q->whereNull('shipping_fee')->orWhereNull('paid_amount')->orWhereNull('currency'))->count(),
            // Phase 3/4 attention counters retained for later activation.
            // 'contacts' => \App\Models\TradingContact::where(fn ($q) => $q->whereNull('phone')->orWhere('phone', '')->orWhere('phone_conflict', true)->orWhere('opt_in', false))->count(),
            // 'failed' => \App\Models\WhatsappMessage::where('status', 'failed')->count(),
            // 'reviews' => \App\Models\FactoryAccount::where('status', 'pending')->count() + \App\Models\CatalogItem::where('status', 'pending')->count(),
        ];
        $recentTrips = LandingTrip::withCount('shipments')->latest('id')->limit(5)->get();

        return view('dashboard.index', compact('tripsCount', 'shipmentsCount', 'merchantsCount', 'leadsCount', 'attention', 'recentTrips'));
    }
}
