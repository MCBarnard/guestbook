<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Support\Facades\Auth;

class MessagesUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public $message;
    public $user;
    public $action;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($message, $user, $action)
    {
        $this->message = $message;
        $this->user = $user;
        $this->action = $action;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return PrivateChannel[]
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('messages.admin'),
            new PrivateChannel('messages.' . $this->user->id)
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->message->id,
            'message' => $this->message->message,
            'from_admin' => Auth::user()->is_admin,
            'action' => $this->action,
            'opened' => $this->message->opened,
            'user_id' => $this->message->user->id,
            'name' => $this->message->user->name,
            'email' => $this->message->user->email,
            'created_at' =>  date_format($this->message->created_at,"H:i d M Y ")
        ];
    }
}
