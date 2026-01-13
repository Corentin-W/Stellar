<?php

namespace App\Events;

use App\Models\RoboTargetSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoboTargetShotRunning implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public RoboTargetSession $session,
        public array $shotData = []
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
        return 'shot.running';
    }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->session->id,
            'shot' => [
                'file' => $this->shotData['File'] ?? null,
                'exposure' => $this->shotData['Expo'] ?? 0,
                'elapsed' => $this->shotData['Elapsed'] ?? 0,
                'elapsed_perc' => $this->shotData['ElapsedPerc'] ?? 0,
                'status' => $this->shotData['Status'] ?? 0, // 1=Exposure, 2=Download, 3=JPG
            ],
            'timestamp' => now()->toISOString(),
        ];
    }
}
