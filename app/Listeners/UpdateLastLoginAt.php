<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;  // ✅ Correct : événement Laravel natif
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateLastLoginAt
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        // Mettre à jour la dernière connexion de l'utilisateur
        $event->user->update([
            'last_login_at' => now()
        ]);
    }
}
