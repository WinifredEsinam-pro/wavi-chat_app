<div class="flex flex-col h-full bg-white">
    {{-- Group Chat Header --}}
@if($activeGroup)
<div class="flex items-center justify-between px-4 py-3 border-b border-gray-300">
    <div class="flex items-center gap-3">
        @if($activeGroup->avatar)
            <img src="{{ Storage::url($activeGroup->avatar) }}" 
                 class="w-10 h-10 rounded-full object-cover">
        @else
            <div class="w-10 h-10 bg-green-200 rounded-full flex items-center justify-center text-green-800 font-semibold">
                <i class="fas fa-users"></i>
            </div>
        @endif
        <div>
            <h2 class="font-semibold text-gray-900">{{ $activeGroup->name }}</h2>
            <p class="text-sm text-gray-500">
                {{ $this->onlineMembersCount }} online
            </p>
        </div>
    </div>

    {{-- Info icon --}}
    <button wire:click="showGroupInfo" class="text-gray-500 hover:text-green-600 transition">
        <i class="fas fa-info-circle text-xl"></i>
    </button>
</div>
@endif

    {{-- Messages Area --}}
    <div id="messagesList" class="flex-1 overflow-auto p-4 space-y-1">
        @if($activeGroup && $messages->count())
            @foreach($messages as $msg)
                @php
                    $isMe = $msg->sender_id == auth()->id();
                @endphp

                <div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }} px-4 py-1">
                    <div class="max-w-[70%] px-4 py-2 rounded-lg {{ $isMe ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700' }}">
                        {{-- Sender name for group messages --}}
                        @if(!$isMe)
                            <div class="text-xs font-semibold text-gray-600 mb-1">
                                {{ $msg->sender->name }}
                            </div>
                        @endif
                        
                        {{-- Message content --}}
                        <div class="text-sm leading-tight">{!! nl2br(e($msg->message)) !!}</div>
                        
                        {{-- Message time --}}
                        <div class="text-xs mt-1 opacity-70 text-right">
                            {{ $msg->created_at->format('H:i') }}
                        </div>
                    </div>
                </div>
            @endforeach
        @elseif($activeGroup)
            <div class="text-center text-gray-500 py-8">
                <i class="fas fa-comments text-3xl mb-2 opacity-50"></i>
                <div class="text-sm">No messages yet</div>
                <div class="text-xs mt-1">Send a message to start the conversation</div>
            </div>
        @else
            <div class="text-center text-gray-500 py-8">
                <i class="fas fa-users text-3xl mb-2 opacity-50"></i>
                <div class="text-sm">Select a group to start messaging</div>
            </div>
        @endif
    </div>

    {{-- Message Input --}}
    @if($activeGroup)
    <div class="border-t border-gray-300">
        <div class="h-16 flex items-center gap-2 px-4">
            <i class="fa-solid fa-face-smile py-2 px-2 hover:bg-gray-100 rounded-full"></i>
             <i class="fas fa-paperclip text-lg hover:bg-gray-100 rounded-full py-1 px-2"></i>
            
            {{-- Message Input --}}
            <input
                wire:model="newMessage"
                wire:keydown.enter="sendGroupMessage"
                type="text"
                placeholder="Type a message..."
                class="flex-1 px-4 py-2 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
            />

            {{-- Send Button --}}
            <button wire:click="sendGroupMessage" 
                    wire:loading.attr="disabled"
                    class="px-3 py-2 bg-green-600 text-white rounded-full hover:bg-green-700 transition disabled:opacity-50">
                <i class="bi bi-send-arrow-up"></i>
            </button>
        </div>
    </div>
    @endif

    {{-- Group Info Modal --}}
    @if($showGroupInfoModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg w-96 max-w-full max-h-[90vh] overflow-y-auto">
            {{-- Group Header --}}
            <div class="bg-green-700 text-white p-6 rounded-t-lg">
                <div class="text-center">
                    @if($activeGroup->avatar)
                        <img src="{{ Storage::url($activeGroup->avatar) }}" 
                             alt="{{ $activeGroup->name }}"
                             class="w-20 h-20 rounded-full object-cover mx-auto border-4 border-white">
                    @else
                        <div class="w-20 h-20 bg-purple-500 rounded-full flex items-center justify-center text-white font-semibold text-2xl mx-auto border-4 border-white">
                            <i class="fas fa-users"></i>
                        </div>
                    @endif
                    <h2 class="text-xl font-semibold mt-3">{{ $activeGroup->name }}</h2>
                    <p class="text-green-200 text-sm mt-1">
                        {{ $activeGroup->members->count() }} members
                    </p>
                </div>
            </div>

            {{-- Group Info Content --}}
            <div class="p-4 space-y-4">
                {{-- Description --}}
                @if($activeGroup->description)
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Description</h3>
                    <p class="text-gray-900">{{ $activeGroup->description }}</p>
                </div>
                @endif

                {{-- Change Group Photo --}}
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Group Photo</h3>
                    <label class="cursor-pointer flex items-center gap-3 p-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        @if($groupAvatar)
                            <img src="{{ $groupAvatar->temporaryUrl() }}" 
                                 alt="New group photo" 
                                 class="w-12 h-12 rounded-full object-cover">
                        @else
                            <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center text-gray-500">
                                <i class="fas fa-camera"></i>
                            </div>
                        @endif
                        <span class="flex-1 text-gray-700">Change Group Photo</span>
                        <input type="file" wire:model="groupAvatar" class="hidden" accept="image/*">
                    </label>
                    @if($groupAvatar)
                        <button wire:click="updateGroupAvatar" 
                                class="w-full mt-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                            Update Photo
                        </button>
                    @endif
                </div>

                {{-- Members List --}}
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">
                        Members ({{ $activeGroup->members->count() }})
                    </h3>
                    <div class="space-y-2 max-h-60 overflow-y-auto">
                        @foreach($activeGroup->members as $member)
                            <div class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-lg">
                                @if($member->profile_picture)
                                    <img src="{{ Storage::url($member->profile_picture) }}" 
                                         alt="{{ $member->name }}"
                                         class="w-8 h-8 rounded-full object-cover">
                                @else
                                    <div class="w-8 h-8 bg-green-200 rounded-full flex items-center justify-center text-green-800 font-medium text-sm">
                                        {{ $member->getInitial() }}
                                    </div>
                                @endif
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900">{{ $member->name }}</div>
                                    <div class="text-xs text-gray-500 flex items-center gap-1">
                                        <div class="w-2 h-2 rounded-full {{ $member->isOnline() ? 'bg-green-500' : 'bg-gray-400' }}"></div>
                                        {{ $member->isOnline() ? 'Online' : 'Offline' }}
                                        @if($member->id == $activeGroup->created_by)
                                            <span class="text-green-600">• Admin</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Group Actions --}}
                @if($activeGroup->isAdmin(auth()->id()))
                <div class="border-t pt-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Admin Actions</h3>
                    <button wire:click="showAddMembersModal" 
                            class="w-full text-left p-3 hover:bg-gray-50 rounded-lg transition flex items-center gap-3">
                        <i class="fas fa-user-plus text-green-600"></i>
                        <span>Add Members</span>
                    </button>
                </div>
                @endif
            </div>

            {{-- Close Button --}}
            <div class="p-4 border-t">
                <button wire:click="closeGroupInfoModal" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Close
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Add Members Modal --}}
    @if($showAddMembersModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg p-6 w-96 max-w-full max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Add Members</h3>
                <button wire:click="closeAddMembersModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="space-y-3">
                @foreach($this->availableUsers as $user)
                    @if(!$activeGroup->isMember($user->id))
                    <label class="flex items-center p-3 hover:bg-gray-50 border border-gray-200 rounded-lg">
                        <input type="checkbox" 
                               wire:model="selectedNewMembers" 
                               value="{{ $user->id }}" 
                               class="mr-3 rounded border-gray-300 text-green-600 focus:ring-green-500">
                        <div class="flex items-center gap-3">
                            @if($user->profile_picture)
                                <img src="{{ Storage::url($user->profile_picture) }}" 
                                     alt="{{ $user->name }}"
                                     class="w-8 h-8 rounded-full object-cover">
                            @else
                                <div class="w-8 h-8 bg-green-200 rounded-full flex items-center justify-center text-green-800 font-medium text-sm">
                                    {{ $user->getInitial() }}
                                </div>
                            @endif
                            <span class="text-gray-900">{{ $user->name }}</span>
                        </div>
                    </label>
                    @endif
                @endforeach
            </div>
            
            <div class="flex gap-2 justify-end pt-4">
                <button wire:click="closeAddMembersModal" 
                        class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button wire:click="addMembersToGroup" 
                        wire:loading.attr="disabled"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition disabled:opacity-50">
                    <span wire:loading.remove>Add Members</span>
                    <span wire:loading>Adding...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Group Creation Modal --}}
    @if($showCreateGroupModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg p-6 w-96 max-w-full max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Create New Group</h3>
                <button wire:click="closeCreateGroupModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="space-y-4">
                <!-- Group Avatar -->
                <div class="text-center">
                    <label class="cursor-pointer">
                        @if($groupAvatar)
                            <img src="{{ $groupAvatar->temporaryUrl() }}" 
                                 alt="Group preview" 
                                 class="w-20 h-20 rounded-full object-cover mx-auto border-2 border-green-500">
                        @else
                            <div class="w-20 h-20 bg-purple-500 rounded-full flex items-center justify-center text-white font-semibold text-2xl mx-auto">
                                <i class="fas fa-users"></i>
                            </div>
                        @endif
                        <input type="file" wire:model="groupAvatar" class="hidden" accept="image/*">
                        <div class="text-xs text-gray-500 mt-2">Click to add group photo</div>
                    </label>
                </div>

                <!-- Group Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Group Name *</label>
                    <input type="text" 
                           wire:model="groupName" 
                           placeholder="Enter group name"
                           class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                
                <!-- Group Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
                    <textarea wire:model="groupDescription" 
                              placeholder="What's this group about?"
                              class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                              rows="3"></textarea>
                </div>
                
                <!-- Add Members -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Add Members *</label>
                    <div class="max-h-40 overflow-y-auto border border-gray-300 rounded-lg">
                        @foreach($this->users as $user)
                            <label class="flex items-center p-3 hover:bg-gray-50 border-b border-gray-100 last:border-b-0">
                                <input type="checkbox" 
                                       wire:model="selectedUsers" 
                                       value="{{ $user->id }}" 
                                       class="mr-3 rounded border-gray-300 text-green-600 focus:ring-green-500">
                                <div class="flex items-center gap-3">
                                    @if($user->profile_picture)
                                        <img src="{{ Storage::url($user->profile_picture) }}" 
                                             alt="{{ $user->name }}"
                                             class="w-8 h-8 rounded-full object-cover">
                                    @else
                                        <div class="w-8 h-8 bg-green-200 rounded-full flex items-center justify-center text-green-800 font-medium text-sm">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <span class="text-gray-900">{{ $user->name }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="flex gap-2 justify-end pt-4">
                    <button wire:click="closeCreateGroupModal" 
                            class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button wire:click="createGroup" 
                            wire:loading.attr="disabled"
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition disabled:opacity-50">
                        <span wire:loading.remove>Create Group</span>
                        <span wire:loading>Creating...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>