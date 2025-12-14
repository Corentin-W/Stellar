<?php

namespace App\Events;

use App\Models\RoboTargetSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoboTargetProgress implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public RoboTargetSession $session,
        public array $progressData
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
        return 'session.progress';
    }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->session->id,
            'progress' => $this->progressData,
            'timestamp' => now()->toISOString(),
        ];
    }
}
