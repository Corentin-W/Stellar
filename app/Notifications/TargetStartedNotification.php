<?php

namespace App\Notifications;

use App\Models\RoboTarget;
use App\Models\RoboTargetSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TargetStartedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public RoboTarget $target,
        public RoboTargetSession $session
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🌌 Votre target "' . $this->target->target_name . '" est en cours !')
            ->view('emails.target-started', [
                'target' => $this->target,
                'session' => $this->session,
                'user' => $notifiable,
            ]);
    }

    /**
     * Get the array representation of the notification (for database/in-app).
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'target_started',
            'title' => 'Target en cours d\'acquisition',
            'message' => 'Votre target "' . $this->target->target_name . '" est maintenant active.',
            'target_id' => $this->target->id,
            'target_guid' => $this->target->guid,
            'target_name' => $this->target->target_name,
            'session_id' => $this->session->id,
            'started_at' => $this->session->session_start?->toISOString(),
            'action_url' => route('robotarget.monitor', [
                'locale' => app()->getLocale(),
                'guid' => $this->target->guid
            ]),
            'icon' => '🔭',
        ];
    }
}
