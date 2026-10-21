<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueueUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $jadwalId,
        public string $tanggalKunjungan
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('queue-updates'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'QueueUpdated';
    }
}
