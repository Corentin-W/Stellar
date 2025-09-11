<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LanguageController;

/*
|--------------------------------------------------------------------------
| Web Routes - AstroSphere
|--------------------------------------------------------------------------
*/

// Route de base - redirection vers la locale par défaut
Route::get('/', function () {
    return redirect('/' . config('app.locale', 'fr'));
});

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
});

// ======================
// CHANGEMENT DE LANGUE
// ======================

Route::get('/lang/{locale}', [LanguageController::class, 'switchLang'])
    ->where('locale', 'fr|en')
    ->name('lang.switch');

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
