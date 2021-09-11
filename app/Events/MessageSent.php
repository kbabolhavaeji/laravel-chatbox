<?php

namespace App\Events;

use App\Models\Message;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * User that sent the message
     *
     * @var User
     */
    public $user;

    /**
     * Message details
     *
     * @var Message
     */
    public $message;

    /**
     *
     * Room code
     *
     * @var
     */
    public $room_code;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user, Message $message, String $room_code)
    {
        $this->user = $user;
        $this->message = $message;
        $this->room_code = $room_code;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return string[]
     */
    public function broadcastOn()
    {
        return ['codeex-chat'];
    }

    public function broadcastAs()
    {
        return 'MessageSent';
    }
}
