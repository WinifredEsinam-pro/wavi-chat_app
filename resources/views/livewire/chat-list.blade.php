<div class="flex flex-col h-full bg-white">
    <div id="messagesList" class="flex-1 overflow-auto p-4 space-y-1">
        @if($groupedMessages && $groupedMessages->count())
            @foreach($groupedMessages as $date => $dateMessages)
                {{-- Date Separator --}}
                <div class="flex justify-center my-4">
                    <div class="bg-gray-200 text-gray-600 text-xs px-3 py-1 rounded-full">
                        {{ $this->formatMessageDate($date) }}
                    </div>
                </div>

                {{-- Messages for this date --}}
                @foreach($dateMessages as $msg)
                    @php
                        $isMe = $msg->sender_id == auth()->id();
                    @endphp

                    <div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }} px-4 py-1">
                        <div class="max-w-[70%] px-4 py-2 rounded-lg {{ $isMe ? 'bg-green-600 text-gray-700' : 'bg-gray-200 text-gray-700' }}">
                            
                            {{-- Text Message --}}
                            @if($msg->type === 'text')
                                <div class="text-sm leading-tight">{!! nl2br(e($msg->message)) !!}</div>

                            {{-- Image --}}
                            @elseif($msg->is_image)
                                <div class="mb-2">
                                    <img src="{{ Storage::url($msg->file_path) }}" 
                                         alt="Shared image"
                                         class="rounded-lg max-w-full max-h-64 cursor-pointer"
                                         onclick="window.open('{{ Storage::url($msg->file_path) }}', '_blank')">
                                </div>
                                <div class="text-xs opacity-80">
                                    {{ $msg->file_name }}
                                </div>

                            {{-- Video --}}
                            @elseif($msg->is_video)
                                <div class="mb-2">
                                    <video controls class="rounded-lg max-w-full max-h-64">
                                        <source src="{{ Storage::url($msg->file_path) }}" type="{{ $msg->mime_type }}">
                                        Your browser does not support the video tag.
                                    </video>
                                </div>
                                <div class="text-xs opacity-80 flex justify-between">
                                    <span>{{ $msg->file_name }}</span>
                                    @if($msg->duration)
                                        <span>{{ gmdate('i:s', $msg->duration) }}</span>
                                    @endif
                                </div>

                            {{-- Audio --}}
                            @elseif($msg->is_audio)
                                <div class="flex items-center gap-3 p-2 bg-black bg-opacity-20 rounded-lg">
                                    <i class="fas fa-music text-xl"></i>
                                    <audio controls class="flex-1">
                                        <source src="{{ Storage::url($msg->file_path) }}" type="{{ $msg->mime_type }}">
                                    </audio>
                                    @if($msg->duration)
                                        <span class="text-xs">{{ gmdate('i:s', $msg->duration) }}</span>
                                    @endif
                                </div>
                                <div class="text-xs opacity-80 mt-1">
                                    {{ $msg->file_name }}
                                </div>

                            {{-- Contact --}}
                            @elseif($msg->is_contact)
                                @php $contact = $msg->contact_info; @endphp
                                <div class="flex items-center gap-3 p-3 bg-black bg-opacity-20 rounded-lg">
                                    <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center text-white">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-semibold">{{ $contact->name }}</div>
                                        <div class="text-xs opacity-80">{{ $contact->phone }}</div>
                                        @if($contact->email)
                                            <div class="text-xs opacity-80">{{ $contact->email }}</div>
                                        @endif
                                    </div>
                                    <button class="text-white bg-green-500 hover:bg-green-600 px-3 py-1 rounded text-xs">
                                        Save Contact
                                    </button>
                                </div>

                            {{-- Poll --}}
                            @elseif($msg->is_poll)
                                @php $poll = $msg->poll_info; @endphp
                                <div class="space-y-2">
                                    <div class="font-semibold">{{ $poll['question'] }}</div>
                                    <div class="space-y-1">
                                        @foreach($poll['options'] as $index => $option)
                                            <button wire:click="voteOnPoll({{ $msg->id }}, {{ $index }})"
                                                    class="w-full text-left p-2 bg-black bg-opacity-20 rounded hover:bg-opacity-30 transition text-sm">
                                                <div class="flex justify-between">
                                                    <span>{{ $option }}</span>
                                                    <span>{{ $poll['votes'][$index] ?? 0 }} votes</span>
                                                </div>
                                                @if($poll['total_votes'] > 0)
                                                    <div class="w-full bg-gray-300 rounded-full h-1 mt-1">
                                                        <div class="bg-green-500 h-1 rounded-full" 
                                                             style="width: {{ (($poll['votes'][$index] ?? 0) / $poll['total_votes']) * 100 }}%">
                                                        </div>
                                                    </div>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                    <div class="text-xs opacity-80 text-right">
                                        {{ $poll['total_votes'] }} total votes
                                    </div>
                                </div>

                            {{-- Other Files --}}
                            @elseif($msg->is_file)
                                <div class="flex items-center gap-3 p-2 bg-black bg-opacity-20 rounded-lg">
                                    <i class="fas {{ $msg->file_icon }} text-xl"></i>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium truncate {{ $isMe ? 'text-white' : 'text-gray-900' }}">
                                            {{ $msg->file_name }}
                                        </div>
                                        <div class="text-xs {{ $isMe ? 'text-green-200' : 'text-gray-600' }}">
                                            {{ $this->formatFileSize($msg->file_size) }}
                                        </div>
                                    </div>
                                    <a href="{{ route('file.download', $msg->id) }}" 
                                       wire:navigate
                                       class="text-white bg-green-500 hover:bg-green-600 px-2 py-1 rounded text-xs transition">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>
                            @endif
                            
                            {{-- Message Time --}}
                            <div class="text-xs mt-1 opacity-70 text-right flex items-center justify-end gap-1">
                                <span>{{ $msg->created_at->format('H:i') }}</span>
                                @if($isMe)
                                    @php
                                        $statusIcon = $this->getMessageStatus($msg);
                                    @endphp
                                    @if($statusIcon)
                                        <i class="fas {{ $statusIcon }}"></i>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endforeach
        @endif
    </div>

    {{-- Chat Input Area with Media Options --}}
    @if($conversationId)
    <div class="border-t border-gray-300">
        {{-- Upload Progress --}}
        @if($isUploading)
        <div class="px-4 py-2 bg-blue-100 text-blue-700 text-sm">
            <i class="fas fa-spinner fa-spin mr-2"></i>
            Uploading file...
        </div>
        @endif
        
        <div class="h-16 flex items-center gap-2 px-4">
            <i class="fa-solid fa-face-smile py-2 px-2 hover:bg-gray-100 rounded-full"></i>
            {{-- Media Attachment Button --}}
            <div class="relative group">
                <button class="p-2 text-gray-500 hover:text-gray-700 transition">
                    <i class="fas fa-paperclip text-lg hover:bg-gray-100 rounded-full py-1 px-2"></i>
                </button>
                {{-- Attachment Menu --}}
                <div class="absolute bottom-12 left-0 bg-white shadow-lg rounded-lg p-2 min-w-48 hidden group-hover:block border">
                    <label class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded-lg cursor-pointer transition">
                        <i class="fas fa-image text-green-500"></i>
                        <span>Photo/Video</span>
                        <input type="file" 
                               wire:model="uploadedFile"
                               class="hidden"
                               accept="image/*,video/*">
                    </label>
                    <label class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded-lg cursor-pointer transition">
                        <i class="fas fa-music text-green-500"></i>
                        <span>Audio</span>
                        <input type="file" 
                               wire:model="uploadedFile"
                               class="hidden"
                               accept="audio/*">
                    </label>
                    <label class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded-lg cursor-pointer transition">
                        <i class="fas fa-file text-green-500"></i>
                        <span>Document</span>
                        <input type="file" 
                               wire:model="uploadedFile"
                               class="hidden"
                               accept=".pdf,.doc,.docx,.txt,.xls,.xlsx">
                    </label>
                    <button wire:click="$set('showContactModal', true)" 
                            class="w-full flex items-center gap-3 p-2 hover:bg-gray-100 rounded-lg transition text-left">
                        <i class="fas fa-user text-green-500"></i>
                        <span>Share Contact</span>
                    </button>
                    <button wire:click="$set('showPollModal', true)" 
                            class="w-full flex items-center gap-3 p-2 hover:bg-gray-100 rounded-lg transition text-left">
                        <i class="fas fa-chart-bar text-green-500"></i>
                        <span>Create Poll</span>
                    </button>
                </div>
            </div>

            {{-- Message Input --}}
            <input
                wire:model="newMessage"
                wire:keydown.enter="sendMessage"
                type="text"
                placeholder="Type a message..."
                class="flex-1 px-4 py-2 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
            />

            {{-- Send Button --}}
            <button wire:click="sendMessage" 
                    wire:loading.attr="disabled"
                    class="px-3 py-2 bg-green-600 text-white rounded-full hover:bg-green-700 transition disabled:opacity-50">
                @if($isUploading)
                    <i class="fas fa-spinner fa-spin"></i>
                @else
                    <span wire:loading.remove><i class="bi bi-send-arrow-up"></i></span>
                    <span wire:loading>Sending...</span>
                @endif
            </button>
        </div>
    </div>

    {{-- Poll Modal --}}
    @if($showPollModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-96">
            <h3 class="text-lg font-semibold mb-4">Create Poll</h3>
            <input wire:model="pollQuestion" type="text" placeholder="Poll question" class="w-full p-2 border rounded mb-3">
            <div class="space-y-2 mb-4">
                @foreach($pollOptions as $index => $option)
                <div class="flex gap-2">
                    <input wire:model="pollOptions.{{ $index }}" type="text" placeholder="Option {{ $index + 1 }}" class="flex-1 p-2 border rounded">
                    @if($index >= 2)
                    <button wire:click="removePollOption({{ $index }})" class="p-2 text-red-500">
                        <i class="fas fa-times"></i>
                    </button>
                    @endif
                </div>
                @endforeach
            </div>
            <button wire:click="addPollOption" class="text-green-600 mb-4">+ Add Option</button>
            <div class="flex gap-2 justify-end">
                <button wire:click="resetPoll" class="px-4 py-2 border rounded">Cancel</button>
                <button wire:click="createPoll" class="px-4 py-2 bg-green-600 text-white rounded">Create Poll</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Contact Modal --}}
    @if($showContactModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-96">
            <h3 class="text-lg font-semibold mb-4">Share Contact</h3>
            <input wire:model="contactName" type="text" placeholder="Name" class="w-full p-2 border rounded mb-3">
            <input wire:model="contactPhone" type="text" placeholder="Phone" class="w-full p-2 border rounded mb-3">
            <input wire:model="contactEmail" type="email" placeholder="Email (optional)" class="w-full p-2 border rounded mb-4">
            <div class="flex gap-2 justify-end">
                <button wire:click="resetContact" class="px-4 py-2 border rounded">Cancel</button>
                <button wire:click="shareContact" class="px-4 py-2 bg-green-600 text-white rounded">Share</button>
            </div>
        </div>
    </div>
    @endif

    @else
    <div class="h-16 border-t border-gray-300 flex items-center justify-center px-4 text-gray-500 text-sm">
        Select a conversation to start messaging
    </div>
    @endif
</div>