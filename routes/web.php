<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\WaitingListController;
use App\Http\Controllers\Admin\CreditAdminController;

/*
|--------------------------------------------------------------------------
| Web Routes - AstroSphere
|--------------------------------------------------------------------------
*/

// Route de base - redirection vers la locale par défaut
Route::get('/', function () {
    return redirect('/' . config('app.locale', 'fr'));
});

Route::post('/locale/{newLocale}', [LocaleController::class, 'change'])
    ->where('newLocale', 'fr|en')
    ->name('locale.change');

// Routes avec préfixe de locale
Route::prefix('{locale?}')->where(['locale' => 'fr|en'])->group(function () {


    // ======================
    // ROUTES PUBLIQUES
    // ======================

    // Page d'accueil
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // ======================
    // ROUTES D'AUTHENTIFICATION
    // ======================

    // Affichage des formulaires
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'login'])->name('login');
        Route::get('/register', [AuthController::class, 'register'])->name('register');
        Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    });

    // Traitement des formulaires
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.post');
    Route::post('/register', [AuthController::class, 'store'])->name('register.post');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');

    // Déconnexion
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ======================
    // ROUTES OAUTH/SOCIALITE
    // ======================

    // Redirection vers les providers OAuth
    Route::get('/auth/{provider}', [AuthController::class, 'redirectToProvider'])
        ->where('provider', 'google|github|facebook|twitter')
        ->name('social.login');

    // Callback des providers OAuth
    Route::get('/auth/{provider}/callback', [AuthController::class, 'handleProviderCallback'])
        ->where('provider', 'google|github|facebook|twitter')
        ->name('social.callback');

    // ======================
    // ROUTES AJAX/API
    // ======================

    // Validation en temps réel
    Route::post('/check-email', [AuthController::class, 'checkEmail'])->name('check.email');
    Route::post('/validate-password', [AuthController::class, 'validatePassword'])->name('validate.password');

    // ======================
    // ROUTES PROTÉGÉES
    // ======================

    Route::middleware('auth')->group(function () {
        // Dashboard
        Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');

        // Profil utilisateur
        Route::get('/profile', [HomeController::class, 'profile'])->name('profile');
        Route::put('/profile', [HomeController::class, 'updateProfile'])->name('profile.update');

        // Paramètres
        Route::get('/settings', [HomeController::class, 'settings'])->name('settings');
        Route::put('/settings', [HomeController::class, 'updateSettings'])->name('settings.update');
    });

    // ======================
    // PAGES STATIQUES
    // ======================

    Route::get('/about', [HomeController::class, 'about'])->name('about');
    Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
    Route::post('/contact', [HomeController::class, 'sendContact'])->name('contact.send');
    Route::get('/privacy', [HomeController::class, 'privacy'])->name('privacy');
    Route::get('/terms', [HomeController::class, 'terms'])->name('terms');

    // Redirection des URLs admin localisées vers l'URL canonique non localisée
    Route::get('/admin/{path?}', function ($locale, $path = null) {
        $suffix = $path ? '/' . ltrim($path, '/') : '';
        return redirect('/admin' . $suffix, 302);
    })->where('path', '.*');
});

// ======================
// CHANGEMENT DE LANGUE (ANCIENNE VERSION - SUPPRIMER)
// ======================

Route::get('/lang/{locale}', [LanguageController::class, 'switchLang'])
    ->where('locale', 'fr|en')
    ->name('lang.switch');

// SUPPRIMER CETTE LIGNE - ELLE EST MAINTENANT DANS LE GROUPE
// Route::post('/locale/{locale}', [LocaleController::class, 'change'])->name('locale.change');

// ======================
// ROUTES DE FALLBACK
// ======================

// Redirection des anciennes URLs sans locale
Route::fallback(function () {
    $path = request()->path();

    // Si l'URL ne commence pas par une locale, rediriger avec la locale par défaut
    if (!preg_match('/^(fr|en)\//', $path)) {
        return redirect('/' . config('app.locale', 'fr') . '/' . $path);
    }

    // Sinon, 404
    abort(404);
});

/*
|--------------------------------------------------------------------------
| Routes API (optionnel)
|--------------------------------------------------------------------------
*/

Route::prefix('api')->middleware('api')->group(function () {
    // API pour l'application mobile ou les appels AJAX
    Route::post('/auth/check-email', [AuthController::class, 'checkEmail']);
    Route::post('/auth/validate-password', [AuthController::class, 'validatePassword']);

    // API protégée
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function () {
            return response()->json(auth()->user());
        });
    });
});

// Routes publiques waiting list
Route::group(['prefix' => 'waiting-list'], function () {
    // Afficher le formulaire
    Route::get('/', [WaitingListController::class, 'create'])->name('waiting-list.create');

    // Traiter l'inscription (API endpoint)
    Route::post('/', [WaitingListController::class, 'store'])->name('waiting-list.store');

    // Confirmer l'email
    Route::get('/confirm/{token}', [WaitingListController::class, 'confirm'])->name('waiting-list.confirm');
});

// Routes admin (avec middleware auth + admin)
Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'admin']], function () {
    Route::group(['prefix' => 'waiting-list'], function () {
        // Dashboard admin
        Route::get('/', [WaitingListController::class, 'admin'])->name('admin.waiting-list.dashboard');

        // Export CSV
        Route::get('/export', [WaitingListController::class, 'export'])->name('admin.waiting-list.export');
    });
});


// Routes admin
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/panel', [App\Http\Controllers\AdminController::class, 'panel'])->name('panel');
    Route::post('/login-as/{user}', [App\Http\Controllers\AdminController::class, 'loginAsUser'])->name('login-as');
    Route::post('/toggle-admin/{user}', [App\Http\Controllers\AdminController::class, 'toggleAdmin'])->name('toggle-admin');
    Route::post('/switch-back', [App\Http\Controllers\AdminController::class, 'switchBack'])->name('switch-back');
});



Route::prefix('{locale?}')->where(['locale' => 'fr|en'])->group(function () {

    // Routes existantes...

    // Routes de crédits (authentifiées)
    Route::middleware('auth')->group(function () {

        // Boutique de crédits
        Route::get('/credits/shop', [CreditController::class, 'shop'])->name('credits.shop');
        Route::get('/credits/package/{package}', [CreditController::class, 'packageDetails'])->name('credits.package.details');
        Route::post('/credits/validate-promotion', [CreditController::class, 'validatePromotion'])->name('credits.validate-promotion');
        Route::post('/credits/purchase', [CreditController::class, 'purchase'])->name('credits.purchase');

        // Historique et gestion
        Route::get('/credits/history', [CreditController::class, 'history'])->name('credits.history');
        Route::get('/credits/balance', [CreditController::class, 'balance'])->name('credits.balance');

        // Estimation de coûts
        Route::post('/credits/estimate-session', [CreditController::class, 'estimateSessionCost'])->name('credits.estimate-session');

        // Stripe success/cancel
        Route::get('/credits/success', function() {
            return view('credits.success');
        })->name('credits.success');

        Route::get('/credits/cancel', function() {
            return view('credits.cancel');
        })->name('credits.cancel');
    });
});

// Routes API (sans préfixe locale)
Route::prefix('api')->middleware(['auth'])->group(function () {

    // API Stripe pour créer Payment Intent
    Route::post('/create-payment-intent', [StripeApiController::class, 'createPaymentIntent']);
    Route::post('/confirm-payment', [StripeApiController::class, 'confirmPayment']);

    // API Crédits
    Route::get('/credits/balance', [CreditController::class, 'balance']);
    Route::post('/credits/estimate', [CreditController::class, 'estimateSessionCost']);
});

// Routes Admin (sans préfixe locale)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard principal des crédits
    Route::get('/credits', [CreditAdminController::class, 'dashboard'])->name('credits.dashboard');

    // Gestion des packages
    Route::prefix('credits/packages')->name('credits.packages.')->group(function () {
        Route::get('/', [CreditAdminController::class, 'packages'])->name('index');
        Route::get('/create', [CreditAdminController::class, 'createPackage'])->name('create');
        Route::post('/', [CreditAdminController::class, 'storePackage'])->name('store');
        Route::get('/{package}/edit', [CreditAdminController::class, 'editPackage'])->name('edit');
        Route::put('/{package}', [CreditAdminController::class, 'updatePackage'])->name('update');
        Route::post('/{package}/toggle-status', [CreditAdminController::class, 'togglePackageStatus'])->name('toggle-status');
        Route::delete('/{package}', [CreditAdminController::class, 'deletePackage'])->name('delete');
    });

    // Gestion des promotions
    Route::prefix('credits/promotions')->name('credits.promotions.')->group(function () {
        Route::get('/', [CreditAdminController::class, 'promotions'])->name('index');
        Route::get('/create', [CreditAdminController::class, 'createPromotion'])->name('create');
        Route::post('/', [CreditAdminController::class, 'storePromotion'])->name('store');
        Route::get('/{promotion}/edit', [CreditAdminController::class, 'editPromotion'])->name('edit');
        Route::put('/{promotion}', [CreditAdminController::class, 'updatePromotion'])->name('update');
        Route::post('/{promotion}/toggle-status', [CreditAdminController::class, 'togglePromotionStatus'])->name('toggle-status');
        Route::delete('/{promotion}', [CreditAdminController::class, 'deletePromotion'])->name('delete');
    });

    // Gestion des utilisateurs et ajustements
    Route::prefix('credits/users')->name('credits.users.')->group(function () {
        Route::get('/', [CreditAdminController::class, 'users'])->name('index');
        Route::get('/{user}', [CreditAdminController::class, 'userDetails'])->name('details');
        Route::post('/{user}/adjust-credits', [CreditAdminController::class, 'adjustUserCredits'])->name('adjust-credits');
    });

    // Rapports et analytics
    Route::get('/credits/reports', [CreditAdminController::class, 'reports'])->name('credits.reports');
    Route::get('/credits/export', [CreditAdminController::class, 'exportTransactions'])->name('credits.export');

    // Transactions
    Route::get('/credits/transactions', [CreditAdminController::class, 'transactions'])->name('credits.transactions');
});

// Webhooks Stripe (sans middleware auth)
// Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])->name('stripe.webhook');
