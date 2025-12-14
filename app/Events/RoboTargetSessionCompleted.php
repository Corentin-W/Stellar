<?php

namespace App\Events;

use App\Models\RoboTargetSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoboTargetSessionCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public RoboTargetSession $session,
        public array $completionData = []
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
        return 'session.completed';
    }

    public function broadcastWith(): array
    {
        return [
            'session' => [
                'id' => $this->session->id,
                'guid' => $this->session->session_guid,
                'target_name' => $this->session->roboTarget->target_name,
                'result' => $this->session->result,
                'images_captured' => $this->session->images_captured,
                'images_accepted' => $this->session->images_accepted,
                'duration' => $this->session->getDuration(),
                'completed_at' => $this->session->session_end?->toISOString(),
            ],
            'completion_data' => $this->completionData,
            'timestamp' => now()->toISOString(),
        ];
    }
}
