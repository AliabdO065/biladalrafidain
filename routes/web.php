<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LandingDashboardController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\MerchantAccountController;
use App\Http\Controllers\ShipmentImportController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

//frontend
Route::group(['as'=>'fronted.', 'middleware'=>['setlocale'] ], function () {
// PHASE 3/4 deferred:     Route::get('/catalog', [\App\Http\Controllers\CatalogController::class,'index'])->name('catalog');
// PHASE 3/4 deferred:     Route::get('/catalog/factories', [\App\Http\Controllers\CatalogController::class,'factories'])->name('catalog.factories');
// PHASE 3/4 deferred:     Route::get('/catalog/items/{id}', [\App\Http\Controllers\CatalogController::class,'show'])->whereNumber('id')->name('catalog.item');
// PHASE 3/4 deferred:     Route::get('/catalog/factories/{id}', [\App\Http\Controllers\CatalogController::class,'factory'])->whereNumber('id')->name('catalog.factory');
// PHASE 3/4 deferred:     Route::get('/catalog/photos/{photo}', [\App\Http\Controllers\CatalogController::class,'photo'])->name('catalog.photo');

    Route::get('/', [LandingController::class,'index'])->name('index');
    Route::post('/callback', [LandingController::class,'submitCallback'])->name('landing.callback');
    Route::get('/lang/{code}', [LandingController::class,'setLocale'])->whereIn('code', \App\Models\Language::SUPPORTED)->name('setLocale');
    Route::get('/tracking', [LandingController::class,'tracking'])->middleware('throttle:30,1')->name('tracking');
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
Route::group(['prefix'=>'admin','as'=>'dashboard.','middleware'=>['auth:web','setadminlocale']], function () {

Route::get('/', [HomeController::class,'index'])->name('index');
Route::view('/help', 'dashboard.help')->name('help');
// PHASE 3/4 deferred: Route::get('/catalog', [\App\Http\Controllers\CatalogAdminController::class,'index'])->name('catalog.index');
// PHASE 3/4 deferred: Route::post('/catalog/factories/{factory}', [\App\Http\Controllers\CatalogAdminController::class,'factory'])->name('catalog.factory');
// PHASE 3/4 deferred: Route::post('/catalog/items/{item}', [\App\Http\Controllers\CatalogAdminController::class,'item'])->name('catalog.item');
// PHASE 3/4 deferred: Route::post('/catalog/categories', [\App\Http\Controllers\CatalogAdminController::class,'category'])->name('catalog.category.create');
// PHASE 3/4 deferred: Route::post('/catalog/categories/{category}', [\App\Http\Controllers\CatalogAdminController::class,'category'])->name('catalog.category.save');
// PHASE 3/4 deferred: Route::get('/imports', [\App\Http\Controllers\TradeController::class,'index'])->name('trade.index');
// PHASE 3/4 deferred: Route::post('/imports/preview', [\App\Http\Controllers\TradeController::class,'preview'])->name('trade.upload');
// PHASE 3/4 deferred: Route::get('/imports/{import}', [\App\Http\Controllers\TradeController::class,'review'])->name('trade.preview');
// PHASE 3/4 deferred: Route::post('/imports/{import}/confirm', [\App\Http\Controllers\TradeController::class,'confirm'])->name('trade.confirm');
// PHASE 3/4 deferred: Route::get('/merchants', [\App\Http\Controllers\TradeController::class,'contacts'])->name('trade.contacts');
// PHASE 3/4 deferred: Route::post('/merchants/{contact}', [\App\Http\Controllers\TradeController::class,'saveContact'])->name('trade.contacts.save');
// PHASE 3/4 deferred: Route::get('/merchants/{contact}/movements', [\App\Http\Controllers\TradeController::class,'movements'])->name('trade.movements');
// PHASE 3/4 deferred: Route::get('/whatsapp/templates', [\App\Http\Controllers\TradeController::class,'templates'])->name('trade.templates');
// PHASE 3/4 deferred: Route::post('/whatsapp/templates/{template}', [\App\Http\Controllers\TradeController::class,'saveTemplate'])->name('trade.templates.save');
// PHASE 3/4 deferred: Route::post('/whatsapp/templates/{template}/verify', [\App\Http\Controllers\TradeController::class,'verifyTemplate'])->name('trade.templates.verify');
// PHASE 3/4 deferred: Route::get('/whatsapp/messages', [\App\Http\Controllers\TradeController::class,'messages'])->name('trade.messages');
// PHASE 3/4 deferred: Route::post('/whatsapp/messages/{message}/send', [\App\Http\Controllers\TradeController::class,'send'])->name('trade.messages.send');
// PHASE 3/4 deferred: Route::post('/whatsapp/messages/{message}/cancel', [\App\Http\Controllers\TradeController::class,'cancel'])->name('trade.messages.cancel');
// PHASE 3/4 deferred: Route::post('/whatsapp/messages/{message}/refresh', [\App\Http\Controllers\TradeController::class,'refresh'])->name('trade.messages.refresh');
// PHASE 3/4 deferred: Route::post('/whatsapp/reply', [\App\Http\Controllers\TradeController::class,'reply'])->middleware('throttle:30,1')->name('trade.reply');
// PHASE 3/4 deferred: Route::get('/shipments/{shipment}/document', [\App\Http\Controllers\TradeController::class,'document'])->name('trade.document');
// PHASE 3/4 deferred: Route::post('/shipments/{shipment}/notifications/prepare', [\App\Http\Controllers\TradeController::class,'prepareShipment'])->name('trade.shipment.prepare');
// PHASE 3/4 deferred: Route::post('/trips/{trip}/documents/prepare', [\App\Http\Controllers\TradeController::class,'prepareTrip'])->name('trade.trip.documents');
// PHASE 3/4 deferred: Route::get('/shipments/{shipment}/image', [\App\Http\Controllers\TradeController::class,'image'])->middleware('throttle:10,1')->name('trade.image');
Route::get('/trips', [TripController::class, 'index'])->name('trips.index');
Route::get('/trips/data', [TripController::class, 'data'])->name('trips.data');
Route::post('/trips', [TripController::class, 'store'])->name('trips.store');
Route::get('/trips/{trip}', [TripController::class, 'show'])->name('trips.show');
Route::delete('/trips/{trip}', [TripController::class, 'destroy'])->name('trips.destroy');
Route::post('/trips/{trip}/update', [TripController::class, 'update'])->name('trips.update');
Route::post('/trips/{trip}/advance', [TripController::class, 'advance'])->name('trips.advance');
// PHASE 3/4 deferred: Route::get('/accounts', [MerchantAccountController::class, 'index'])->name('accounts.index');
// PHASE 3/4 deferred: Route::get('/accounts/data', [MerchantAccountController::class, 'data'])->name('accounts.data');
// PHASE 3/4 deferred: Route::get('/accounts/history', [MerchantAccountController::class, 'history'])->name('accounts.history');
// PHASE 3/4 deferred: Route::get('/accounts/trips/{trip}', [MerchantAccountController::class, 'tripReview'])->name('accounts.trip');
// PHASE 3/4 deferred: Route::post('/accounts/trips/{trip}/prepare', [MerchantAccountController::class, 'prepareTrip'])->name('accounts.trip.prepare');
// PHASE 3/4 deferred: Route::get('/accounts/merchant/{key}', [MerchantAccountController::class, 'merchant'])->where('key', '[a-f0-9]{64}')->name('accounts.merchant');
// PHASE 3/4 deferred: Route::post('/accounts/merchant/{key}/prepare', [MerchantAccountController::class, 'prepare'])->where('key', '[a-f0-9]{64}')->name('accounts.prepare');
// PHASE 3/4 deferred: Route::get('/accounts/{batch}', [MerchantAccountController::class, 'show'])->name('accounts.show');
// PHASE 3/4 deferred: Route::post('/accounts/{batch}/send', [MerchantAccountController::class, 'send'])->name('accounts.send');
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
    Route::delete('/landing/stats/delete/{id}', [LandingDashboardController::class,'deleteStat'])->name('landing.stats.delete');

    Route::get('/landing/services', [LandingDashboardController::class,'services'])->name('landing.services');
    Route::get('/landing/services/add', [LandingDashboardController::class,'addService'])->name('landing.services.add');
    Route::post('/landing/services/store', [LandingDashboardController::class,'storeService'])->name('landing.services.store');
    Route::get('/landing/services/edit/{id}', [LandingDashboardController::class,'editService'])->name('landing.services.edit');
    Route::post('/landing/services/update/{id}', [LandingDashboardController::class,'updateService'])->name('landing.services.update');
    Route::delete('/landing/services/delete/{id}', [LandingDashboardController::class,'deleteService'])->name('landing.services.delete');

    Route::get('/landing/steps', [LandingDashboardController::class,'steps'])->name('landing.steps');
    Route::get('/landing/steps/add', [LandingDashboardController::class,'addStep'])->name('landing.steps.add');
    Route::post('/landing/steps/store', [LandingDashboardController::class,'storeStep'])->name('landing.steps.store');
    Route::get('/landing/steps/edit/{id}', [LandingDashboardController::class,'editStep'])->name('landing.steps.edit');
    Route::post('/landing/steps/update/{id}', [LandingDashboardController::class,'updateStep'])->name('landing.steps.update');
    Route::delete('/landing/steps/delete/{id}', [LandingDashboardController::class,'deleteStep'])->name('landing.steps.delete');

    Route::get('/landing/comparisons', [LandingDashboardController::class,'comparisons'])->name('landing.comparisons');
    Route::get('/landing/comparisons/add', [LandingDashboardController::class,'addComparison'])->name('landing.comparisons.add');
    Route::post('/landing/comparisons/store', [LandingDashboardController::class,'storeComparison'])->name('landing.comparisons.store');
    Route::get('/landing/comparisons/edit/{id}', [LandingDashboardController::class,'editComparison'])->name('landing.comparisons.edit');
    Route::post('/landing/comparisons/update/{id}', [LandingDashboardController::class,'updateComparison'])->name('landing.comparisons.update');
    Route::delete('/landing/comparisons/delete/{id}', [LandingDashboardController::class,'deleteComparison'])->name('landing.comparisons.delete');

    Route::get('/landing/reviews', [LandingDashboardController::class,'reviews'])->name('landing.reviews');
    Route::get('/landing/reviews/add', [LandingDashboardController::class,'addReview'])->name('landing.reviews.add');
    Route::post('/landing/reviews/store', [LandingDashboardController::class,'storeReview'])->name('landing.reviews.store');
    Route::get('/landing/reviews/edit/{id}', [LandingDashboardController::class,'editReview'])->name('landing.reviews.edit');
    Route::post('/landing/reviews/update/{id}', [LandingDashboardController::class,'updateReview'])->name('landing.reviews.update');
    Route::delete('/landing/reviews/delete/{id}', [LandingDashboardController::class,'deleteReview'])->name('landing.reviews.delete');

    Route::get('/landing/faqs', [LandingDashboardController::class,'faqs'])->name('landing.faqs');
    Route::get('/landing/faqs/add', [LandingDashboardController::class,'addFaq'])->name('landing.faqs.add');
    Route::post('/landing/faqs/store', [LandingDashboardController::class,'storeFaq'])->name('landing.faqs.store');
    Route::get('/landing/faqs/edit/{id}', [LandingDashboardController::class,'editFaq'])->name('landing.faqs.edit');
    Route::post('/landing/faqs/update/{id}', [LandingDashboardController::class,'updateFaq'])->name('landing.faqs.update');
    Route::delete('/landing/faqs/delete/{id}', [LandingDashboardController::class,'deleteFaq'])->name('landing.faqs.delete');

    Route::get('/landing/leads', [LandingDashboardController::class,'leads'])->name('landing.leads');
    Route::delete('/landing/leads/delete/{id}', [LandingDashboardController::class,'deleteLead'])->name('landing.leads.delete');

    Route::get('/landing/policies', [LandingDashboardController::class,'policies'])->name('landing.policies');
    Route::get('/landing/policies/add', [LandingDashboardController::class,'addPolicy'])->name('landing.policies.add');
    Route::post('/landing/policies/store', [LandingDashboardController::class,'storePolicy'])->name('landing.policies.store');
    Route::get('/landing/policies/edit/{id}', [LandingDashboardController::class,'editPolicy'])->name('landing.policies.edit');
    Route::post('/landing/policies/update/{id}', [LandingDashboardController::class,'updatePolicy'])->name('landing.policies.update');
    Route::delete('/landing/policies/delete/{id}', [LandingDashboardController::class,'deletePolicy'])->name('landing.policies.delete');

    Route::get('/landing/shipments', [LandingDashboardController::class,'shipments'])->name('landing.shipments');
    Route::get('/landing/shipments/data', [LandingDashboardController::class,'shipmentsData'])->name('landing.shipments.data');
    // Shipment file upload paused; use the normal shipment entry form.
    // Route::post('/landing/shipments/import', [ShipmentImportController::class,'store'])->name('landing.shipments.import');
    Route::get('/landing/shipments/add', [LandingDashboardController::class,'addShipment'])->name('landing.shipments.add');
    Route::post('/landing/shipments/store', [LandingDashboardController::class,'storeShipment'])->name('landing.shipments.store');
    Route::get('/landing/shipments/edit/{id}', [LandingDashboardController::class,'editShipment'])->name('landing.shipments.edit');
    Route::post('/landing/shipments/update/{id}', [LandingDashboardController::class,'updateShipment'])->name('landing.shipments.update');
    Route::post('/landing/shipments/{id}/advance', [LandingDashboardController::class,'advanceShipment'])->name('landing.shipments.advance');
    Route::post('/landing/shipments/delete/{id}', [LandingDashboardController::class,'deleteShipment'])->name('landing.shipments.delete');
    Route::post('/landing/shipments/{id}/events/store', [LandingDashboardController::class,'storeShipmentEvent'])->name('landing.shipments.events.store');
    Route::delete('/landing/shipments/{id}/events/delete/{eventId}', [LandingDashboardController::class,'deleteShipmentEvent'])->name('landing.shipments.events.delete');
});


//login 
// PHASE 4: Factory portal and PHASE 3: WhatsApp webhooks retained below.
// Route::group(['prefix'=>'factory','as'=>'factory.','middleware'=>['setlocale'],'where'=>['id'=>'[0-9]+']],function() {
//     Route::get('/login',[\App\Http\Controllers\FactoryPortalController::class,'loginForm'])->name('login');
//     Route::post('/login',[\App\Http\Controllers\FactoryPortalController::class,'login'])->middleware('throttle:8,1,factory-login:')->name('login.submit');
//     Route::get('/register',[\App\Http\Controllers\FactoryPortalController::class,'registerForm'])->name('register');
//     Route::post('/register',[\App\Http\Controllers\FactoryPortalController::class,'register'])->middleware('throttle:5,60,factory-register:')->name('register.submit');
//     Route::middleware('factory.auth')->group(function() {
//         Route::get('/',[\App\Http\Controllers\FactoryPortalController::class,'home'])->name('home');
//         Route::post('/logout',[\App\Http\Controllers\FactoryPortalController::class,'logout'])->name('logout');
//         Route::post('/profile',[\App\Http\Controllers\FactoryPortalController::class,'profile'])->name('profile');
//         Route::post('/password',[\App\Http\Controllers\FactoryPortalController::class,'password'])->middleware('throttle:8,1,factory-password:')->name('password');
//         Route::get('/items/create',[\App\Http\Controllers\FactoryPortalController::class,'create'])->name('items.create');
//         Route::post('/items',[\App\Http\Controllers\FactoryPortalController::class,'store'])->middleware('throttle:20,1,factory-write:')->name('items.store');
//         Route::get('/items/{id}/edit',[\App\Http\Controllers\FactoryPortalController::class,'edit'])->name('items.edit');
//         Route::post('/items/{id}',[\App\Http\Controllers\FactoryPortalController::class,'update'])->middleware('throttle:20,1,factory-write:')->name('items.update');
//         Route::delete('/items/{id}',[\App\Http\Controllers\FactoryPortalController::class,'delete'])->name('items.delete');
//     });
// });
// Route::get('/whatsapp/webhook', [\App\Http\Controllers\WhatsAppWebhookController::class,'verify'])->middleware('throttle:120,1');
// Route::post('/whatsapp/webhook', [\App\Http\Controllers\WhatsAppWebhookController::class,'receive'])->middleware('throttle:6000,1');
Route::get('/login/lang/{code}', function (string $code) {
    session(['admin_locale' => $code]);
    return redirect()->route('login');
})->whereIn('code', \App\Models\Language::SUPPORTED)->name('login.setLocale');
Route::get('login', 'App\Http\Controllers\Auth\LoginController@showLoginForm')->middleware('setadminlocale')->name('login');
Route::post('login', 'App\Http\Controllers\Auth\LoginController@login')->middleware('setadminlocale');
//logout
Route::post('/logout', 'App\Http\Controllers\Auth\LoginController@logout')->middleware('auth:web')->name('logout');

