<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LandingDashboardController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

//frontend
Route::group(['as'=>'fronted.', 'middleware'=>['setlocale'] ], function () {

    Route::get('/', [LandingController::class,'index'])->name('index');
    Route::post('/callback', [LandingController::class,'submitCallback'])->name('landing.callback');
    Route::get('/lang/{code}', [LandingController::class,'setLocale'])->name('setLocale');
    Route::get('/tracking', [LandingController::class,'tracking'])->name('tracking');
    Route::get('/policies', [LandingController::class,'policies'])->name('policies');
    Route::get('/home', function () {
        return redirect()->route('fronted.index');
    });
    // Legacy multi-page routes — content now lives on the single-page site above.
    // Kept as named redirects (rather than removed) so any bookmarked/indexed URLs still resolve.
    Route::get('/about', fn () => redirect()->route('fronted.index'))->name('about');
    Route::get('/services', fn () => redirect()->route('fronted.index'))->name('services');
    Route::get('/projects', fn () => redirect()->route('fronted.index'))->name('projects');
    Route::get('/projects/details/{i}', fn () => redirect()->route('fronted.index'))->name('projectDetails');
    Route::get('/news', fn () => redirect()->route('fronted.index'))->name('news');
    Route::get('/onlynews', fn () => redirect()->route('fronted.index'))->name('onlynews');
    Route::get('/onlyinsight', fn () => redirect()->route('fronted.index'))->name('onlyinsight');
    Route::get('/news/details/{i}', fn () => redirect()->route('fronted.index'))->name('newsDetails');
    Route::get('/contact', fn () => redirect()->route('fronted.index'))->name('contact');

});


//backend
Route::group(['prefix'=>'admin','as'=>'dashboard.','middleware'=>['auth','setadminlocale']], function () {

Route::get('/', [HomeController::class,'index'])->name('index');
Route::get('/lang/{code}', function (string $code) {
    if (in_array($code, ['ar', 'en'], true)) {
        session(['admin_locale' => $code]);
    }
    return redirect()->back();
})->name('setLocale');

///////////////////////////////////////////////////////////////////////////////////////////////////////
// Landing page (the live public homepage)
    Route::get('/landing/languages', [LandingDashboardController::class,'languages'])->name('landing.languages');
    Route::post('/landing/languages/update', [LandingDashboardController::class,'updateLanguages'])->name('landing.languages.update');

    Route::get('/landing/settings', [LandingDashboardController::class,'settings'])->name('landing.settings');
    Route::post('/landing/settings/update', [LandingDashboardController::class,'updateSettings'])->name('landing.settings.update');

    Route::get('/landing/about', [LandingDashboardController::class,'about'])->name('landing.about');

    Route::get('/landing/stats', [LandingDashboardController::class,'stats'])->name('landing.stats');
    Route::get('/landing/stats/add', [LandingDashboardController::class,'addStat'])->name('landing.stats.add');
    Route::post('/landing/stats/store', [LandingDashboardController::class,'storeStat'])->name('landing.stats.store');
    Route::get('/landing/stats/edit/{id}', [LandingDashboardController::class,'editStat'])->name('landing.stats.edit');
    Route::post('/landing/stats/update/{id}', [LandingDashboardController::class,'updateStat'])->name('landing.stats.update');
    Route::get('/landing/stats/delete/{id}', [LandingDashboardController::class,'deleteStat'])->name('landing.stats.delete');

    Route::get('/landing/services', [LandingDashboardController::class,'services'])->name('landing.services');
    Route::get('/landing/services/add', [LandingDashboardController::class,'addService'])->name('landing.services.add');
    Route::post('/landing/services/store', [LandingDashboardController::class,'storeService'])->name('landing.services.store');
    Route::get('/landing/services/edit/{id}', [LandingDashboardController::class,'editService'])->name('landing.services.edit');
    Route::post('/landing/services/update/{id}', [LandingDashboardController::class,'updateService'])->name('landing.services.update');
    Route::get('/landing/services/delete/{id}', [LandingDashboardController::class,'deleteService'])->name('landing.services.delete');

    Route::get('/landing/steps', [LandingDashboardController::class,'steps'])->name('landing.steps');
    Route::get('/landing/steps/add', [LandingDashboardController::class,'addStep'])->name('landing.steps.add');
    Route::post('/landing/steps/store', [LandingDashboardController::class,'storeStep'])->name('landing.steps.store');
    Route::get('/landing/steps/edit/{id}', [LandingDashboardController::class,'editStep'])->name('landing.steps.edit');
    Route::post('/landing/steps/update/{id}', [LandingDashboardController::class,'updateStep'])->name('landing.steps.update');
    Route::get('/landing/steps/delete/{id}', [LandingDashboardController::class,'deleteStep'])->name('landing.steps.delete');

    Route::get('/landing/comparisons', [LandingDashboardController::class,'comparisons'])->name('landing.comparisons');
    Route::get('/landing/comparisons/add', [LandingDashboardController::class,'addComparison'])->name('landing.comparisons.add');
    Route::post('/landing/comparisons/store', [LandingDashboardController::class,'storeComparison'])->name('landing.comparisons.store');
    Route::get('/landing/comparisons/edit/{id}', [LandingDashboardController::class,'editComparison'])->name('landing.comparisons.edit');
    Route::post('/landing/comparisons/update/{id}', [LandingDashboardController::class,'updateComparison'])->name('landing.comparisons.update');
    Route::get('/landing/comparisons/delete/{id}', [LandingDashboardController::class,'deleteComparison'])->name('landing.comparisons.delete');

    Route::get('/landing/reviews', [LandingDashboardController::class,'reviews'])->name('landing.reviews');
    Route::get('/landing/reviews/add', [LandingDashboardController::class,'addReview'])->name('landing.reviews.add');
    Route::post('/landing/reviews/store', [LandingDashboardController::class,'storeReview'])->name('landing.reviews.store');
    Route::get('/landing/reviews/edit/{id}', [LandingDashboardController::class,'editReview'])->name('landing.reviews.edit');
    Route::post('/landing/reviews/update/{id}', [LandingDashboardController::class,'updateReview'])->name('landing.reviews.update');
    Route::get('/landing/reviews/delete/{id}', [LandingDashboardController::class,'deleteReview'])->name('landing.reviews.delete');

    Route::get('/landing/faqs', [LandingDashboardController::class,'faqs'])->name('landing.faqs');
    Route::get('/landing/faqs/add', [LandingDashboardController::class,'addFaq'])->name('landing.faqs.add');
    Route::post('/landing/faqs/store', [LandingDashboardController::class,'storeFaq'])->name('landing.faqs.store');
    Route::get('/landing/faqs/edit/{id}', [LandingDashboardController::class,'editFaq'])->name('landing.faqs.edit');
    Route::post('/landing/faqs/update/{id}', [LandingDashboardController::class,'updateFaq'])->name('landing.faqs.update');
    Route::get('/landing/faqs/delete/{id}', [LandingDashboardController::class,'deleteFaq'])->name('landing.faqs.delete');

    Route::get('/landing/leads', [LandingDashboardController::class,'leads'])->name('landing.leads');
    Route::get('/landing/leads/delete/{id}', [LandingDashboardController::class,'deleteLead'])->name('landing.leads.delete');

    Route::get('/landing/policies', [LandingDashboardController::class,'policies'])->name('landing.policies');
    Route::get('/landing/policies/add', [LandingDashboardController::class,'addPolicy'])->name('landing.policies.add');
    Route::post('/landing/policies/store', [LandingDashboardController::class,'storePolicy'])->name('landing.policies.store');
    Route::get('/landing/policies/edit/{id}', [LandingDashboardController::class,'editPolicy'])->name('landing.policies.edit');
    Route::post('/landing/policies/update/{id}', [LandingDashboardController::class,'updatePolicy'])->name('landing.policies.update');
    Route::get('/landing/policies/delete/{id}', [LandingDashboardController::class,'deletePolicy'])->name('landing.policies.delete');

    Route::get('/landing/shipments', [LandingDashboardController::class,'shipments'])->name('landing.shipments');
    Route::get('/landing/shipments/add', [LandingDashboardController::class,'addShipment'])->name('landing.shipments.add');
    Route::post('/landing/shipments/store', [LandingDashboardController::class,'storeShipment'])->name('landing.shipments.store');
    Route::get('/landing/shipments/edit/{id}', [LandingDashboardController::class,'editShipment'])->name('landing.shipments.edit');
    Route::post('/landing/shipments/update/{id}', [LandingDashboardController::class,'updateShipment'])->name('landing.shipments.update');
    Route::get('/landing/shipments/delete/{id}', [LandingDashboardController::class,'deleteShipment'])->name('landing.shipments.delete');
    Route::post('/landing/shipments/{id}/events/store', [LandingDashboardController::class,'storeShipmentEvent'])->name('landing.shipments.events.store');
    Route::get('/landing/shipments/{id}/events/delete/{eventId}', [LandingDashboardController::class,'deleteShipmentEvent'])->name('landing.shipments.events.delete');
});


//login 
Route::get('login', 'App\Http\Controllers\Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'App\Http\Controllers\Auth\LoginController@login');
//logout
Route::get('/logout', 'App\Http\Controllers\Auth\LoginController@logout')->name('logout');

