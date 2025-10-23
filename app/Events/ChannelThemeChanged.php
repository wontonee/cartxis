<?php

namespace App\Events;

use Vortex\Core\Models\Channel;
use Illuminate\Broadcasting\Channel as BroadcastChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChannelThemeChanged
{
    use Dispatchable, SerializesModels;

    public Channel $channel;

    /**
     * Create a new event instance.
     */
    public function __construct(Channel $channel)
    {
        $this->channel = $channel;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, BroadcastChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new BroadcastChannel('admin'),
        ];
    }
}
