<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LanguageController;
use Illuminate\Support\Facades\Route;

// Route pour changer de langue
Route::get('locale/{locale}', [LanguageController::class, 'setLocale'])->name('locale');

// Routes avec préfixe de langue (sans middleware car géré par le ServiceProvider)
Route::group(['prefix' => '{locale?}'], function () {

    // Page d'accueil
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // Routes d'authentification
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/login', [AuthController::class, 'authenticate']);
        Route::get('/register', [AuthController::class, 'register'])->name('register');
        Route::post('/register', [AuthController::class, 'store']);

        // Socialite routes
        Route::get('/auth/{provider}', [AuthController::class, 'redirectToProvider'])->name('social.login');
        Route::get('/auth/{provider}/callback', [AuthController::class, 'handleProviderCallback'])->name('social.callback');
    });

    // Routes protégées
    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');
    });
});

// Redirection par défaut
Route::get('/', function () {
    return redirect('/fr');
});
