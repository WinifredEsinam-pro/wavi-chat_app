<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use App\Events\MessageSent;

class ChatList extends Component
{
    public $users;
    public $conversationId = null;
    public $activeUser = null;
    public $messages;
    public $newMessage = '';

    protected $listeners = [
        'openConversation',
        'messageReceived' => 'handleBroadcastedMessage'
    ];

    public function mount()
    {
        $this->users = User::orderBy('name')->get();
        $this->messages = collect();
    }

    public function openConversation($userId)
    {
        $me = Auth::id();
        $otherId = (int) $userId;

        // Find or create conversation
        $conversation = Conversation::where(function($q) use ($me, $otherId) {
            $q->where('user_one_id', $me)->where('user_two_id', $otherId);
        })->orWhere(function($q) use ($me, $otherId) {
            $q->where('user_one_id', $otherId)->where('user_two_id', $me);
        })->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'user_one_id' => $me,
                'user_two_id' => $otherId
            ]);
        }

        $this->conversationId = $conversation->id;
        $this->activeUser = User::find($otherId);
        $this->loadMessages();

        $this->dispatch('listenOnConversation', conversationId: $this->conversationId);
        $this->dispatch('messagesLoaded');
    }

    protected function loadMessages()
    {
        if (!$this->conversationId) {
            $this->messages = collect();
            return;
        }

        $this->messages = Message::where('conversation_id', $this->conversationId)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function sendMessage()
    {
        if (trim($this->newMessage) === '' || !$this->conversationId) {
            return;
        }

        try {
            $message = Message::create([
                'conversation_id' => $this->conversationId,
                'sender_id' => Auth::id(),
                'message' => trim($this->newMessage),
            ]);

            $message->load('sender');
            broadcast(new MessageSent($message))->toOthers();
            $this->messages->push($message);
            $this->newMessage = '';
            $this->dispatch('messageAppended');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to send message: ' . $e->getMessage());
        }
    }

    // ✅ SIMPLER FIX: Use no parameters and get data from request
public function handleBroadcastedMessage()
{
    $event = request('event') ?? request()->all();
    
    if (isset($event['conversation_id']) && 
        $event['conversation_id'] == $this->conversationId && 
        $event['sender_id'] != Auth::id()) {
        
        $message = new Message([
            'id' => $event['id'],
            'conversation_id' => $event['conversation_id'],
            'sender_id' => $event['sender_id'],
            'message' => $event['message'],
            'created_at' => $event['created_at'],
        ]);
        
        $message->setRelation('sender', (object) $event['sender']);
        $this->messages->push($message);
        $this->dispatch('messageAppended');
    }
}

    public function render()
    {
        return view('livewire.chat-list');
    }
}