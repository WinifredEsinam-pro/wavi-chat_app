<?php

// namespace App\Events;

// use Illuminate\Broadcasting\Channel;
// use Illuminate\Broadcasting\InteractsWithSockets;
// use Illuminate\Broadcasting\PresenceChannel;
// use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
// use Illuminate\Foundation\Events\Dispatchable;
// use Illuminate\Queue\SerializesModels;
// use App\Models\GroupMessage;

// class GroupMessageSent implements ShouldBroadcast
// {
//     use Dispatchable, InteractsWithSockets, SerializesModels;

//     public $groupMessage;

//     public function __construct(GroupMessage $groupMessage)
//     {
//         $this->groupMessage = $groupMessage;
//         $this->groupMessage->load('sender');
//     }

//     public function broadcastOn()
//     {
//         return new PresenceChannel('group.' . $this->groupMessage->group_id);
//     }

//     public function broadcastWith()
//     {
//         return [
//             'id' => $this->groupMessage->id,
//             'group_id' => $this->groupMessage->group_id,
//             'sender_id' => $this->groupMessage->sender_id,
//             'sender' => [
//                 'id' => $this->groupMessage->sender->id,
//                 'name' => $this->groupMessage->sender->name,
//                 'profile_picture' => $this->groupMessage->sender->profile_picture,
//             ],
//             'message' => $this->groupMessage->message,
//             'type' => $this->groupMessage->type,
//             'created_at' => $this->groupMessage->created_at->toDateTimeString(),
//         ];
//     }
// }