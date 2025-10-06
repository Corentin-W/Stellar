<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\WaitingListController;
use App\Http\Controllers\Admin\OrderAdminController;
use App\Http\Controllers\Admin\CreditAdminController;
use App\Http\Controllers\Admin\BookingAdminController;
use App\Http\Controllers\Admin\ProductAdminController;
use App\Http\Controllers\Admin\SupportAdminController;
use App\Http\Controllers\Admin\SupportReportController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\SupportCategoryController;
use App\Http\Controllers\Admin\SupportTemplateController;
use App\Http\Controllers\Admin\ProductPromotionController;

/*
|--------------------------------------------------------------------------
| Web Routes - AstroSphere
|--------------------------------------------------------------------------
*/

// Route de base - redirection vers la locale par défaut
Route::get('/', function () {
    return redirect('/' . config('app.locale', 'fr'));
});

// Changement de locale
Route::post('/locale/{newLocale}', [LocaleController::class, 'change'])
    ->where('newLocale', 'fr|en')
    ->name('locale.change');

/*
|--------------------------------------------------------------------------
| Routes avec préfixe de locale
|--------------------------------------------------------------------------
*/

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

        // ======================
        // ROUTES DE CRÉDITS
        // ======================

        // Boutique de crédits
        Route::get('/credits/shop', [CreditController::class, 'shop'])->name('credits.shop');
        Route::get('/credits/package/{package}', [CreditController::class, 'packageDetails'])->name('credits.package.details');
        Route::post('/credits/validate-promotion', [CreditController::class, 'validatePromotion'])->name('credits.validate-promotion');

        // Historique et gestion
        Route::get('/credits/history', [CreditController::class, 'history'])->name('credits.history');
        Route::get('/credits/balance', [CreditController::class, 'balance'])->name('credits.balance');

        // Estimation de coûts
        Route::post('/credits/estimate-session', [CreditController::class, 'estimateSessionCost'])->name('credits.estimate-session');

        // Pages de résultat d'achat
        Route::get('/credits/success', function() {
            return view('credits.success');
        })->name('credits.success');

        Route::get('/credits/cancel', function() {
            return view('credits.cancel');
        })->name('credits.cancel');


        // ======================
        // ROUTES DE SUPPORT UTILISATEURS
        // ======================

        Route::prefix('support')->name('support.')->group(function () {
            Route::get('/', [SupportController::class, 'index'])->name('index');

            // Créer un nouveau ticket
            Route::get('/create', [SupportController::class, 'create'])->name('create');
            Route::post('/', [SupportController::class, 'store'])->name('store');

            // Afficher un ticket spécifique
            Route::get('/{ticket}', [SupportController::class, 'show'])->name('show');

            // Répondre à un ticket
            Route::post('/{ticket}/reply', [SupportController::class, 'reply'])->name('reply');

            // Actions sur les tickets
            Route::post('/{ticket}/close', [SupportController::class, 'close'])->name('close');
            Route::post('/{ticket}/reopen', [SupportController::class, 'reopen'])->name('reopen');

            // Téléchargement de fichiers joints
            Route::get('/attachment/{attachment}/download', [SupportController::class, 'downloadAttachment'])
                ->name('attachment.download');
        });
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

/*
|--------------------------------------------------------------------------
| Routes API
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

// API Crédits (authentifiées)
Route::prefix('api')->middleware(['auth'])->group(function () {
    Route::prefix('credits')->name('api.credits.')->group(function () {
        // Créer un Payment Intent
        Route::post('/create-payment-intent', [CreditController::class, 'createPaymentIntent'])
             ->name('create-payment-intent');

        // Confirmer un paiement
        Route::post('/confirm-payment', [CreditController::class, 'confirmPayment'])
             ->name('confirm-payment');

        // Valider une promotion
        Route::post('/validate-promotion', [CreditController::class, 'validatePromotion'])
             ->name('validate-promotion');

        // Obtenir le solde actuel
        Route::get('/balance', [CreditController::class, 'balance'])
             ->name('balance');

        // Estimer le coût d'une session
        Route::post('/estimate-session', [CreditController::class, 'estimateSessionCost'])
             ->name('estimate-session');
    });
});

/*
|--------------------------------------------------------------------------
| Routes Waiting List
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => 'waiting-list'], function () {
    // Afficher le formulaire
    Route::get('/', [WaitingListController::class, 'create'])->name('waiting-list.create');

    // Traiter l'inscription (API endpoint)
    Route::post('/', [WaitingListController::class, 'store'])->name('waiting-list.store');

    // Confirmer l'email
    Route::get('/confirm/{token}', [WaitingListController::class, 'confirm'])->name('waiting-list.confirm');
});

// Routes admin waiting list
Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'admin']], function () {
    Route::group(['prefix' => 'waiting-list'], function () {
        // Dashboard admin
        Route::get('/', [WaitingListController::class, 'admin'])->name('admin.waiting-list.dashboard');

        // Export CSV
        Route::get('/export', [WaitingListController::class, 'export'])->name('admin.waiting-list.export');
    });
});

/*
|--------------------------------------------------------------------------
| Routes Admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Panel admin général
    Route::get('/panel', [App\Http\Controllers\AdminController::class, 'panel'])->name('panel');
    Route::post('/login-as/{user}', [App\Http\Controllers\AdminController::class, 'loginAsUser'])->name('login-as');
    Route::post('/toggle-admin/{user}', [App\Http\Controllers\AdminController::class, 'toggleAdmin'])->name('toggle-admin');
    Route::post('/switch-back', [App\Http\Controllers\AdminController::class, 'switchBack'])->name('switch-back');

    // ======================
    // ROUTES ADMIN CRÉDITS
    // ======================

    // Dashboard crédits
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
    Route::get('/credits/transactions', [CreditAdminController::class, 'transactions'])->name('credits.transactions');


    // ======================
    // ROUTES ADMIN SUPPORT
    // ======================

    Route::prefix('support')->name('support.')->group(function () {
        // Dashboard principal du support
        Route::get('/', [SupportAdminController::class, 'dashboard'])->name('dashboard');

        // Gestion des tickets
        Route::prefix('tickets')->name('tickets.')->group(function () {
            Route::get('/', [SupportAdminController::class, 'tickets'])->name('index');
            Route::get('/{ticket}', [SupportAdminController::class, 'show'])->name('show');
            Route::post('/{ticket}/reply', [SupportAdminController::class, 'reply'])->name('reply');
            Route::post('/{ticket}/assign', [SupportAdminController::class, 'assign'])->name('assign');
            Route::post('/{ticket}/priority', [SupportAdminController::class, 'changePriority'])->name('change-priority');
            Route::post('/{ticket}/category', [SupportAdminController::class, 'changeCategory'])->name('change-category');
            Route::get('/attachment/{attachment}/download', [SupportAdminController::class, 'downloadAttachment'])->name('attachment.download');
        });

        // Gestion des catégories
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [SupportCategoryController::class, 'index'])->name('index');
            Route::get('/create', [SupportCategoryController::class, 'create'])->name('create');
            Route::post('/', [SupportCategoryController::class, 'store'])->name('store');
            Route::get('/{category}/edit', [SupportCategoryController::class, 'edit'])->name('edit');
            Route::put('/{category}', [SupportCategoryController::class, 'update'])->name('update');
            Route::post('/{category}/toggle-status', [SupportCategoryController::class, 'toggleStatus'])->name('toggle-status');
            Route::delete('/{category}', [SupportCategoryController::class, 'destroy'])->name('destroy');
            Route::post('/reorder', [SupportCategoryController::class, 'reorder'])->name('reorder');
        });

        // Gestion des templates
        Route::prefix('templates')->name('templates.')->group(function () {
            Route::get('/', [SupportTemplateController::class, 'index'])->name('index');
            Route::get('/create', [SupportTemplateController::class, 'create'])->name('create');
            Route::post('/', [SupportTemplateController::class, 'store'])->name('store');
            Route::get('/{template}/edit', [SupportTemplateController::class, 'edit'])->name('edit');
            Route::put('/{template}', [SupportTemplateController::class, 'update'])->name('update');
            Route::post('/{template}/toggle-status', [SupportTemplateController::class, 'toggleStatus'])->name('toggle-status');
            Route::delete('/{template}', [SupportTemplateController::class, 'destroy'])->name('destroy');
            Route::get('/{template}/content', [SupportTemplateController::class, 'getContent'])->name('get-content');
        });

        // Rapports et statistiques
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [SupportReportController::class, 'index'])->name('index');
            Route::get('/period', [SupportReportController::class, 'period'])->name('period');
            Route::get('/agents', [SupportReportController::class, 'agents'])->name('agents');
            Route::get('/categories', [SupportReportController::class, 'categories'])->name('categories');
            Route::get('/export/tickets', [SupportReportController::class, 'exportTickets'])->name('export.tickets');
            Route::get('/export/messages', [SupportReportController::class, 'exportMessages'])->name('export.messages');
        });
    });

    // ======================
    // ROUTES ADMIN EQUIPMENT
    // ======================

    Route::prefix('equipment')->name('equipment.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\EquipmentController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\EquipmentController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\EquipmentController::class, 'store'])->name('store');
        Route::get('/{equipment}', [App\Http\Controllers\Admin\EquipmentController::class, 'show'])->name('show');
        Route::get('/{equipment}/edit', [App\Http\Controllers\Admin\EquipmentController::class, 'edit'])->name('edit');
        Route::put('/{equipment}', [App\Http\Controllers\Admin\EquipmentController::class, 'update'])->name('update');
        Route::delete('/{equipment}', [App\Http\Controllers\Admin\EquipmentController::class, 'destroy'])->name('destroy');

        // Actions rapides
        Route::post('/{equipment}/toggle-status', [App\Http\Controllers\Admin\EquipmentController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{equipment}/toggle-active', [App\Http\Controllers\Admin\EquipmentController::class, 'toggleActive'])->name('toggle-active');
        Route::post('/{equipment}/toggle-featured', [App\Http\Controllers\Admin\EquipmentController::class, 'toggleFeatured'])->name('toggle-featured');
    });
});

/*
|--------------------------------------------------------------------------
| Webhooks et autres routes spéciales
|--------------------------------------------------------------------------
*/

// Webhook Stripe (sans middleware auth - IMPORTANT)
Route::post('/stripe/webhook', [CreditController::class, 'stripeWebhook'])
     ->name('stripe.webhook');

/*
|--------------------------------------------------------------------------
| Routes de fallback et anciennes URLs
|--------------------------------------------------------------------------
*/

// Changement de langue (ancienne version - à supprimer éventuellement)
Route::get('/lang/{locale}', [LanguageController::class, 'switchLang'])
    ->where('locale', 'fr|en')
    ->name('lang.switch');

// Redirection des anciennes URLs sans locale
Route::fallback(function () {
    $path = request()->path();

    if (preg_match('/^(fr|en)\/bookings\/[^\/]+\/cancel$/', $path, $matches)) {
        $locale = $matches[1] ?? config('app.locale', 'fr');
        return redirect('/' . $locale . '/bookings/my-bookings');
    }

    // Si l'URL ne commence pas par une locale, rediriger avec la locale par défaut
    if (!preg_match('/^(fr|en)\//', $path)) {
        return redirect('/' . config('app.locale', 'fr') . '/' . $path);
    }

    // Sinon, 404
    abort(404);
});

// Dans le groupe middleware auth
Route::post('/credits/checkout', [CreditController::class, 'createCheckoutSession'])->name('credits.checkout');
Route::get('/credits/success', [CreditController::class, 'paymentSuccess'])->name('credits.success');
// Webhook Stripe (sans middleware auth)
Route::post('/stripe/webhook', [CreditController::class, 'stripeWebhook'])->name('stripe.webhook');


// Routes utilisateur (avec locale)
Route::prefix('{locale?}')->where(['locale' => 'fr|en'])->group(function () {

    Route::middleware('auth')->group(function () {

        // Réservations utilisateur
        Route::prefix('bookings')->name('bookings.')->group(function () {
            Route::get('/calendar', [BookingController::class, 'calendar'])->name('calendar');
            Route::get('/events', [BookingController::class, 'events'])->name('events');
            Route::get('/time-slots', [BookingController::class, 'timeSlots'])->name('time-slots');
            Route::get('/create', [BookingController::class, 'create'])->name('create');
            Route::post('/', [BookingController::class, 'store'])->name('store');
            Route::get('/my-bookings', [BookingController::class, 'myBookings'])->name('my-bookings');
            Route::get('/{booking}/access', [BookingController::class, 'access'])->name('access');
            Route::post('/{booking}/cancel', [BookingController::class, 'cancel'])->name('cancel');
            Route::get('/{booking}/cancel', function ($locale = null, $booking = null) {
                $targetLocale = $locale ?? app()->getLocale();

                return redirect()->route('bookings.my-bookings', ['locale' => $targetLocale]);
            })->name('cancel.redirect');
        });

    });
});

// Routes admin (sans locale)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::prefix('bookings')->name('bookings.')->group(function () {
        // Dashboard
        Route::get('/', [BookingAdminController::class, 'dashboard'])->name('dashboard');

          // Blocages
        Route::get('/blackouts', [BookingAdminController::class, 'blackouts'])->name('blackouts');
        Route::post('/blackouts', [BookingAdminController::class, 'storeBlackout'])->name('blackouts.store');
        Route::delete('/blackouts/{blackout}', [BookingAdminController::class, 'destroyBlackout'])->name('blackouts.destroy');
        
        Route::get('/{booking}', [BookingAdminController::class, 'show'])->name('show');

        // Actions sur les réservations
        Route::post('/{booking}/confirm', [BookingAdminController::class, 'confirm'])->name('confirm');
        Route::post('/{booking}/reject', [BookingAdminController::class, 'reject'])->name('reject');

        // Calendrier admin
        Route::get('/calendar/view', [BookingAdminController::class, 'calendar'])->name('calendar');
        Route::get('/calendar/events', [BookingAdminController::class, 'calendarEvents'])->name('calendar-events');

        // Plages horaires
        Route::get('/equipment/{equipment}/time-slots', [BookingAdminController::class, 'timeSlots'])->name('time-slots');
        Route::post('/equipment/{equipment}/time-slots', [BookingAdminController::class, 'storeTimeSlot'])->name('time-slots.store');
        Route::delete('/time-slots/{timeSlot}', [BookingAdminController::class, 'destroyTimeSlot'])->name('time-slots.destroy');


    });

});
