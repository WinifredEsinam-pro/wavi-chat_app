<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->message->conversation_id),
        ];
    }

    public function broadcastWith(): array
    {
        // Ensure sender relationship is loaded
        if (!$this->message->relationLoaded('sender')) {
            $this->message->load('sender');
        }

        $data = [
            'id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_id' => $this->message->sender_id,
            'type' => $this->message->type,
            'message' => $this->message->message,
            'created_at' => $this->message->created_at->toDateTimeString(),
            'sender' => [
                'id' => $this->message->sender->id,
                'name' => $this->message->sender->name,
            ],
        ];

        // Add file data if it's a file message
        if ($this->message->type === 'file') {
            $data['file_path'] = $this->message->file_path;
            $data['file_name'] = $this->message->file_name;
            $data['file_size'] = $this->message->file_size;
            $data['mime_type'] = $this->message->mime_type;
        }

        return $data;
    }

    public function broadcastAs(): string
    {
        return 'MessageSent';
    }
}