<?php

namespace App\Events;

use App\Models\RoboTargetSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoboTargetSessionStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public RoboTargetSession $session,
        public array $voyagerData = []
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->session->roboTarget->user_id),
            new PrivateChannel('robotarget.session.' . $this->session->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'session.started';
    }

    public function broadcastWith(): array
    {
        return [
            'session' => [
                'id' => $this->session->id,
                'guid' => $this->session->session_guid,
                'target_id' => $this->session->robo_target_id,
                'target_name' => $this->session->roboTarget->target_name,
                'started_at' => $this->session->session_start?->toISOString(),
            ],
            'voyager' => $this->voyagerData,
            'timestamp' => now()->toISOString(),
        ];
    }
}
