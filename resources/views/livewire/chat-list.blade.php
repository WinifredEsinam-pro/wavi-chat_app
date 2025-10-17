<div class="flex flex-col h-full">
    <div id="messagesList" class="flex-1 overflow-auto p-4 space-y-4">
        @if($messages && $messages->count())
            @foreach($messages as $msg)
                @php
                    $isMe = $msg->sender_id == auth()->id();
                @endphp

                <div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[85%] md:max-w-[70%] px-4 py-2 rounded-lg {{ $isMe ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-900' }}">
                        <div class="text-sm leading-tight">{!! nl2br(e($msg->message)) !!}</div>
                        <div class="text-xs mt-1 opacity-70 text-right">
                            {{ $msg->created_at->format('H:i') }}
                        </div>
                    </div>
                </div>
            @endforeach
        @elseif($conversationId)
            <div class="text-center text-gray-500 mt-8">No messages yet. Start the conversation!</div>
        @else
            <div class="text-center text-gray-500 mt-8">Select a user from the left to start chatting.</div>
        @endif
    </div>

    @if($conversationId)
    <div class="h-16 border-t border-gray-300 flex items-center gap-2 px-4">
        <input
            wire:model="newMessage"
            wire:keydown.enter="sendMessage"
            type="text"
            placeholder="Type a message..."
            class="flex-1 px-4 py-2 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
        />
        <button wire:click="sendMessage" 
                wire:loading.attr="disabled"
                class="px-4 py-2 bg-green-600 text-white rounded-full hover:bg-green-700 transition disabled:opacity-50">
            <span wire:loading.remove>Send</span>
            <span wire:loading>Sending...</span>
        </button>
    </div>
    @else
    <div class="h-16 border-t border-gray-300 flex items-center justify-center px-4 text-gray-500 text-sm">
        Select a conversation to start messaging
    </div>
    @endif
</div>