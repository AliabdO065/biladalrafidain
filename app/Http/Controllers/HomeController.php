<?php

namespace App\Http\Controllers;

use App\Models\LandingFaq;
use App\Models\LandingLead;
use App\Models\LandingReview;
use App\Models\LandingService;

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
        $servicesCount = LandingService::count();
        $reviewsCount = LandingReview::count();
        $faqsCount = LandingFaq::count();
        $leadsCount = LandingLead::count();

        return view('dashboard.index', compact('servicesCount', 'reviewsCount', 'faqsCount', 'leadsCount'));
    }
}
