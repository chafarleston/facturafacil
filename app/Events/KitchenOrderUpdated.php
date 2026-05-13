<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KitchenOrderUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $companyId;
    public string $action;
    public array $orders;

    public function __construct(int $companyId, string $action, array $orders)
    {
        $this->companyId = $companyId;
        $this->action = $action;
        $this->orders = $orders;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('kitchen.' . $this->companyId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'KitchenOrderUpdated';
    }
}