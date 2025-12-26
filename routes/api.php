<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\RoboTargetController;
use App\Http\Controllers\Api\PricingController;
use App\Http\Controllers\Api\VoyagerEventController;
use App\Http\Controllers\RoboTargetSetController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::get('/subscriptions/plans', [SubscriptionController::class, 'plans']);

// Target Templates (public pour le catalogue)
Route::get('/target-templates', [App\Http\Controllers\Api\TargetTemplateController::class, 'index']);

// Webhooks (pas d'auth) - Événements temps réel Voyager
Route::prefix('voyager/events')->group(function () {
    Route::post('/session-started', [VoyagerEventController::class, 'sessionStarted']);
    Route::post('/progress', [VoyagerEventController::class, 'progress']);
    Route::post('/image-ready', [VoyagerEventController::class, 'imageReady']);
    Route::post('/session-completed', [VoyagerEventController::class, 'sessionCompleted']);
});

// Ancien webhook (pas d'auth)
Route::post('/webhooks/robotarget/session-complete', [RoboTargetController::class, 'webhookSessionComplete']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // Subscriptions
    Route::prefix('subscriptions')->group(function () {
        Route::get('/current', [SubscriptionController::class, 'current']);
        Route::post('/subscribe', [SubscriptionController::class, 'subscribe']);
        Route::put('/change-plan', [SubscriptionController::class, 'changePlan']);
        Route::post('/cancel', [SubscriptionController::class, 'cancel']);
    });

    // Pricing
    Route::prefix('pricing')->group(function () {
        Route::post('/estimate', [PricingController::class, 'estimate']);
        Route::post('/recommend', [PricingController::class, 'recommend']);
    });

    // RoboTarget
    Route::prefix('robotarget')->group(function () {
        // Targets
        Route::get('/targets', [RoboTargetController::class, 'index']);
        Route::post('/targets', [RoboTargetController::class, 'store']);
        Route::get('/targets/{guid}', [RoboTargetController::class, 'show']);
        Route::post('/targets/{guid}/submit', [RoboTargetController::class, 'submit']);
        Route::delete('/targets/{guid}/cancel', [RoboTargetController::class, 'cancel']);
        Route::get('/targets/{guid}/progress', [RoboTargetController::class, 'progress']);

        // Gallery & Images
        Route::get('/gallery', [RoboTargetController::class, 'getUserGallery']);
        Route::get('/targets/{targetId}/shots', [RoboTargetController::class, 'getTargetShots']);
        Route::get('/sessions/{sessionGuid}/shots', [RoboTargetController::class, 'getSessionShots']);
        Route::get('/shots/{shotGuid}/jpg', [RoboTargetController::class, 'downloadShotJpg']);
        Route::get('/shots/{shotGuid}/metadata', [RoboTargetController::class, 'getShotMetadata']);

        // Stats
        Route::get('/stats', [RoboTargetController::class, 'stats']);

        // Sets - Gestion complète des Sets avec MAC automatique
        Route::get('/sets', [RoboTargetSetController::class, 'index']);
        Route::post('/sets', [RoboTargetSetController::class, 'store']);
        Route::get('/sets/{guid}', [RoboTargetSetController::class, 'show']);
        Route::put('/sets/{guid}', [RoboTargetSetController::class, 'update']);
        Route::delete('/sets/{guid}', [RoboTargetSetController::class, 'destroy']);
        Route::post('/sets/{guid}/enable', [RoboTargetSetController::class, 'enable']);
        Route::post('/sets/{guid}/disable', [RoboTargetSetController::class, 'disable']);
        Route::get('/profiles/{profileName}/sets', [RoboTargetSetController::class, 'byProfile']);
        Route::get('/status', [RoboTargetSetController::class, 'status']);
    });
});
