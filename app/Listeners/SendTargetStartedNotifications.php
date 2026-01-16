<?php

namespace App\Listeners;

use App\Events\RoboTargetSessionStarted;
use App\Notifications\TargetStartedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendTargetStartedNotifications implements ShouldQueue
{
    use InteractsWithQueue;

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
    public function handle(RoboTargetSessionStarted $event): void
    {
        $session = $event->session;
        $target = $session->roboTarget;
        $user = $target->user;

        // Log l'envoi de notification
        Log::info('Sending target started notification', [
            'user_id' => $user->id,
            'target_id' => $target->id,
            'target_name' => $target->target_name,
            'session_id' => $session->id,
        ]);

        // Envoyer la notification (mail + database)
        $user->notify(new TargetStartedNotification($target, $session));
    }

    /**
     * Handle a job failure.
     */
    public function failed(RoboTargetSessionStarted $event, \Throwable $exception): void
    {
        Log::error('Failed to send target started notification', [
            'session_id' => $event->session->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
