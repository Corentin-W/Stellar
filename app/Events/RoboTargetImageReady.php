<?php

namespace App\Events;

use App\Models\RoboTargetSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoboTargetImageReady implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public RoboTargetSession $session,
        public array $imageData
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
        return 'image.ready';
    }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->session->id,
            'image' => [
                'filename' => $this->imageData['filename'] ?? null,
                'thumbnail' => $this->imageData['thumbnail'] ?? null,
                'filter' => $this->imageData['filter'] ?? null,
                'exposure' => $this->imageData['exposure'] ?? null,
                'hfd' => $this->imageData['hfd'] ?? null,
                'timestamp' => $this->imageData['timestamp'] ?? null,
            ],
            'timestamp' => now()->toISOString(),
        ];
    }
}
