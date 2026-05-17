<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KitchenOrderUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $companyId;
    public string $type;

    public function __construct(int $companyId, string $type = 'kitchen')
    {
        $this->companyId = $companyId;
        $this->type = $type;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('kitchen.' . $this->companyId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.updated';
    }
}
