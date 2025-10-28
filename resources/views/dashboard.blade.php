<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Wavi Chat</title>
    <link rel="icon" type="image/png" href="{{ asset('image/wavi_logo.png') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Wavi Chat</title>
    <link rel="icon" type="image/png" href="{{ asset('image/wavi_logo.png') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Add Alpine.js -->
    <script src="//unpkg.com/alpinejs" defer></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        @media (max-width: 768px) {
            .sidebar-mobile {
                transform: translateX(0);
                transition: transform 0.3s ease-in-out;
            }
            .sidebar-mobile.hidden {
                transform: translateX(-100%);
            }
            .chat-area-mobile {
                transform: translateX(0);
                transition: transform 0.3s ease-in-out;
            }
            .chat-area-mobile.hidden {
                transform: translateX(100%);
            }
        }
    </style>
</head>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        @media (max-width: 768px) {
            .sidebar-mobile {
                transform: translateX(0);
                transition: transform 0.3s ease-in-out;
            }
            .sidebar-mobile.hidden {
                transform: translateX(-100%);
            }
            .chat-area-mobile {
                transform: translateX(0);
                transition: transform 0.3s ease-in-out;
            }
            .chat-area-mobile.hidden {
                transform: translateX(100%);
            }
        }
    </style>
</head>
<body class="bg-white text-gray-900">

<div class="flex h-screen">
    {{-- LEFT: Sidebar --}}
    <aside id="sidebar" class="sidebar-mobile w-full md:w-[380px] bg-gray-50 border-r border-gray-300 flex flex-col md:static absolute inset-0 z-10 md:z-auto">
        
     {{-- Sidebar Header --}}
<div class="p-4 border-b border-gray-300 flex items-center justify-between bg-white">
    {{-- Profile Display --}}
    <div class="flex items-center gap-3">
        @auth
           @if(auth()->user()->profile_picture)
    <img src="{{ Storage::url(auth()->user()->profile_picture) }}?v={{ time() }}" 
         alt="Profile" 
         class="w-10 h-10 rounded-full object-cover border-2 border-green-500">
@else
                <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-semibold text-lg">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
            @endif
            <div class="text-left">
                <div class="font-medium text-black-900">{{ auth()->user()->name }}</div>
                <div class="flex items-center gap-1 text-xs text-gray-500">
                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                    <span>Online</span>
                </div>
            </div>
        @endauth
    </div>

    {{-- Vertical Ellipsis Menu --}}
    <div class="relative group" x-data="{ open: false }">
        <button @click="open = !open" class="flex items-center gap-3 text-gray-500 hover:text-gray-700 transition p-2 rounded-lg hover:bg-gray-100">
            <i class="fas fa-ellipsis-vertical text-lg"></i>
        </button>
        
        {{-- Dropdown Menu --}}
        <div x-show="open" 
             @click.away="open = false"
             class="absolute right-0 top-12 bg-white shadow-lg rounded-lg p-2 min-w-48 border z-20"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="transform opacity-0 scale-95"
             x-transition:enter-end="transform opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="transform opacity-100 scale-100"
             x-transition:leave-end="transform opacity-0 scale-95">
            
            {{-- Change Profile Picture --}}
            <button onclick="Livewire.dispatch('showProfileModal')" 
                    @click="open = false"
                    class="w-full flex items-center gap-3 p-2 hover:bg-gray-100 rounded-lg transition text-left">
                <i class="fas fa-user-edit text-green-500"></i>
                <span>Change Profile Picture</span>
            </button>
            
{{-- New Group Button --}}
<button onclick="openGroupModal()" 
        @click="open = false"
        class="w-full flex items-center gap-3 p-2 hover:bg-gray-100 rounded-lg transition text-left">
    <i class="fas fa-users text-green-500"></i>
    <span>New Group</span>
</button>
            {{-- Settings --}}
            <button class="w-full flex items-center gap-3 p-2 hover:bg-gray-100 rounded-lg transition text-left">
                <i class="fas fa-cog text-green-500"></i>
                <span>Settings</span>
            </button>
            
            <hr class="my-2">
            
            {{-- Logout --}}
            <a href="{{ route('logout') }}" 
               class="w-full flex items-center gap-3 p-2 hover:bg-gray-100 rounded-lg transition text-left text-red-500"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
</div>

{{-- Logout Form --}}
<form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
    @csrf
</form>
        {{-- Users and Groups List --}}
    <div class="flex-1 overflow-y-auto bg-white">
        {{-- Saved Messages --}}
        <div data-user-id="{{ auth()->id() }}" 
             class="user-item cursor-pointer p-3 hover:bg-gray-100 border-b border-gray-200 flex items-center gap-3 transition-colors">
            <div class="relative">
                <div class="w-12 h-12 bg-blue-200 rounded-full flex items-center justify-center text-blue-800 font-medium text-lg">
                    <i class="fas fa-bookmark"></i>
                </div>
                <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></div>
            </div>
            <div class="flex-1">
                <h3 class="font-semibold text-gray-900">Saved Messages</h3>
                <p class="text-sm text-gray-600">Save notes and messages</p>
                 </div>
        </div>

        {{-- Groups Section --}}
        <div class="border-b border-gray-200">
            <div class="px-4 py-2 bg-gray-100">
                <h3 class="text-sm font-semibold text-gray-700">GROUPS</h3>
            </div>
            
            @php
                $userGroups = auth()->user()->groups()->with(['members', 'creator'])->get();
            @endphp

            @if($userGroups->count() > 0)
                @foreach($userGroups as $group)
                    <div data-group-id="{{ $group->id }}" 
                         class="group-item cursor-pointer p-3 hover:bg-gray-100 border-b border-gray
                         -200 flex items-center gap-3 transition-colors">
                        <div class="relative">
                            @if($group->avatar)
                                <img src="{{ Storage::url($group->avatar) }}" 
                                     alt="{{ $group->name }}"
                                     class="w-12 h-12 rounded-full object-cover">
                            @else
                                <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center text-white font-medium text-lg">
                                    <i class="fas fa-users"></i>
                                </div>
                            @endif
                            <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></div>
                        </div>
                        <div class="flex-1">
 <h3 class="font-semibold text-gray-900">{{ $group->name }}</h3>
                            <p class="text-sm text-gray-600">
                                {{ $group->members->count() }} members
                                @if($group->description)
                                    • {{ Str::limit($group->description, 20) }}
                                @endif
                            </p>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center text-gray-500 py-4">
                    <i class="fas fa-users text-2xl mb-2 opacity-50"></i>
                    <div class="text-sm">No groups yet</div>
                    <div class="text-xs mt-1">Create your first group!</div>
                </div>
            @endif
             </div>

        {{-- All Users Section --}}
        <div>
            <div class="px-4 py-2 bg-gray-100">
                <h3 class="text-sm font-semibold text-gray-700">CONTACTS</h3>
            </div>
            
            @php
                $allUsers = \App\Models\User::where('id', '!=', auth()->id())->orderBy('name')->get();
            @endphp

            @if($allUsers->count() > 0)
                @foreach($allUsers as $u)
                    <div data-user-id="{{ $u->id }}" 
                     class="user-item cursor-pointer p-3 hover:bg-gray-100 border-b border-gray-200 flex items-center gap-3 transition-colors">
                        {{-- Your existing user item code --}}
                        <div class="relative">
                            @if($u->profile_picture)
                                <img src="{{ Storage::url($u->profile_picture) }}?v={{ time() }}" 
                                     alt="{{ $u->name }}"
                                     class="w-12 h-12 rounded-full object-cover">
                            @else
                                <div class="w-12 h-12 bg-green-200 rounded-full flex items-center justify-center text-green-800 font-medium text-lg">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </div>
                            @endif
                            <div class="absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-white {{ $u->isOnline() ? 'bg-green-500' : 'bg-gray-400' }}"></div>
                        </div>
                         <div class="flex-1">
                            <h3 class="font-semibold text-gray-600">{{ $u->name }}</h3>
                            <p class="text-sm text-gray-600">
                                {{ $u->isOnline() ? 'Online' : 'Last seen ' . ($u->last_seen ? $u->last_seen->diffForHumans() : 'long time ago') }}
                            </p>
                        </div>

                        {{-- Unread Message Count --}}
                        @php
                            $unreadCount = \App\Models\Message::whereHas('conversation', function($q) use ($u) {
                                $q->where(function($query) use ($u) {
                                    $query->where('user_one_id', auth()->id())
                                          ->where('user_two_id', $u->id);
                                })->orWhere(function($query) use ($u) {
                                    $query->where('user_one_id', $u->id)
                                     ->where('user_two_id', auth()->id());
                                });
                            })
                            ->where('sender_id', $u->id)
                            ->where('is_read', false)
                            ->count();
                        @endphp
                        
                        @if($unreadCount > 0)
                            <span class="bg-green-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                {{ $unreadCount }}
                            </span>
                        @endif
                    </div>
                @endforeach
                 @else
                <div class="text-center text-gray-500 py-8">
                    <i class="fas fa-users text-3xl mb-2 opacity-50"></i>
                    <div class="text-sm">No other users found</div>
                </div>
            @endif
        </div>
    </div>
    </aside>

    {{-- RIGHT: Chat Area --}}
    <main id="chatArea" class="chat-area-mobile flex-1 bg-white flex flex-col md:static absolute inset-0 z-10 md:z-auto hidden md:flex">
       {{-- Dynamic Chat Header --}}
<div class="h-16 bg-green-700 text-white flex items-center justify-between px-4">
    <div class="flex items-center gap-3">
        <button id="backBtn" class="text-white mr-2 md:hidden">
            <i class="fas fa-arrow-left text-lg"></i>
        </button>
        
        {{-- Header content --}}
        <div class="flex items-center gap-3">
            <div id="headerAvatar" class="relative">
      <i class="fa-solid fa-circle-user fa-xl"></i>
            </div>
            <div>
                
            <div id="headerName" class="font-semibold text-lg">Select a chat</div>
                <div id="headerSub" class="text-sm opacity-80">online</div>
            </div>
        </div>
    </div>

    <div id="headerIcons" class="flex items-center gap-4 text-white">

        <button class="hover:text-gray-200 transition" title="Video call">
            <i class="fas fa-video"></i>
        </button>
        <button class="hover:text-gray-200 transition" title="Voice call">
            <i class="fas fa-phone"></i>
        </button>
        <button class="hover:text-gray-200 transition" title="More options">
            <i class="fas fa-ellipsis-vertical"></i>
        </button>
    </div>
</div>
{{-- Chat Window --}}
<div id="chatWindow" class="flex-1 overflow-y-auto bg-white flex flex-col">
    {{-- One-on-One Chat --}}
    <div id="oneOnOneChat" class="h-full w-full flex flex-col">
        @livewire('chat-list')
    </div>
    
    {{-- Group Chat --}}
    <div id="groupChat" class="hidden h-full w-full flex flex-col">
        @livewire('group-chat')
    </div>
</div>
    </main>
</div>

@livewireScripts
<script>
// Function to show one-on-one chat and hide group chat
function showOneOnOneChat() {
    const oneOnOneChat = document.getElementById('oneOnOneChat');
    const groupChat = document.getElementById('groupChat');
    
    if (oneOnOneChat) oneOnOneChat.classList.remove('hidden');
    if (groupChat) groupChat.classList.add('hidden');
}

// Function to show group chat and hide one-on-one chat
function showGroupChat() {
    const oneOnOneChat = document.getElementById('oneOnOneChat');
    const groupChat = document.getElementById('groupChat');
    
    if (oneOnOneChat) oneOnOneChat.classList.add('hidden');
    if (groupChat) groupChat.classList.remove('hidden');
}

// Group modal function
function openGroupModal() {
    console.log('Opening group modal...');
    Livewire.dispatch('showCreateGroupModal');
}

document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const chatArea = document.getElementById('chatArea');
    const backBtn = document.getElementById('backBtn');
    const userItems = document.querySelectorAll('.user-item');
    const groupItems = document.querySelectorAll('.group-item');
    const headerIcons = document.getElementById('headerIcons');
    const headerStatus = document.getElementById('headerStatus');

    // Mobile screen management
    function showChatArea() {
        if (window.innerWidth < 768) {
            sidebar.classList.add('hidden');
            chatArea.classList.remove('hidden');
        }
    }

    function showSidebar() {
        if (window.innerWidth < 768) {
            sidebar.classList.remove('hidden');
            chatArea.classList.add('hidden');
        }
    }

    // User click handler
userItems.forEach(item => {
    item.addEventListener('click', function() {
        const userId = this.getAttribute('data-user-id');
        const userName = this.querySelector('h3').textContent;
        const userAvatar = this.querySelector('img');
        
        // Update header for individual chat
        document.getElementById('headerName').textContent = userName;
        document.getElementById('headerSub').textContent = 'Online';
        
        // Update avatar for individual chat
        const headerAvatar = document.getElementById('headerAvatar');
        if (userAvatar) {
            headerAvatar.innerHTML = `<img src="${userAvatar.src}" alt="${userName}" class="w-10 h-10 rounded-full object-cover">`;
        } else {
            const initial = userName.charAt(0).toUpperCase();
            headerAvatar.innerHTML = `
                <div class="w-10 h-10 bg-green-200 rounded-full flex items-center justify-center text-green-800 font-medium">
                    ${initial}
                </div>
                <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></div>
            `;
        }
        
        showOneOnOneChat();
        showChatArea();

        // Call Livewire for one-on-one chat
        if (window.Livewire) {
            Livewire.dispatch('openConversation', { userId: userId });
        }
    });
});
    // Group click handler
groupItems.forEach(item => {
    item.addEventListener('click', function() {
        const groupId = this.getAttribute('data-group-id');
        const groupName = this.querySelector('h3').textContent;
        const groupAvatar = this.querySelector('img');
        
        console.log('Group clicked:', groupId, groupName);
        
        // Update header for group
        document.getElementById('headerName').textContent = groupName;
        document.getElementById('headerSub').textContent = 'Group';
        
        // Update avatar for group
        const headerAvatar = document.getElementById('headerAvatar');
        if (groupAvatar) {
            headerAvatar.innerHTML = `<img src="${groupAvatar.src}" alt="${groupName}" class="w-10 h-10 rounded-full object-cover">`;
        } else {
            headerAvatar.innerHTML = `
                <div class="w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center text-white font-medium">
                    <i class="fas fa-users"></i>
                </div>
            `;
        }
        
        showGroupChat();
        showChatArea();

        // Call Livewire for group chat
        if (window.Livewire) {
            console.log('Dispatching openGroupConversation to Livewire');
            Livewire.dispatch('openGroupConversation', { groupId: groupId });
        }
    });
});

   // Back button handler
if (backBtn) {
    backBtn.addEventListener('click', function() {
        showSidebar();
        
        document.getElementById('headerName').textContent = 'Select a chat';
        document.getElementById('headerSub').textContent = 'online';
        document.getElementById('headerAvatar').innerHTML = `
            <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center text-gray-600 font-medium">
                <i class="fas fa-comments"></i>
            </div>
        `;
        
        // Show one-on-one chat
        showOneOnOneChat();
    });
}

    document.addEventListener('messagesLoaded', () => {
        const cw = document.getElementById('chatWindow');
        if (cw) setTimeout(() => cw.scrollTop = cw.scrollHeight, 100);
    });
    
    document.addEventListener('messageAppended', () => {
        const cw = document.getElementById('chatWindow');
        if (cw) setTimeout(() => cw.scrollTop = cw.scrollHeight, 100);
    });

    Livewire.on('profilePictureUpdated', () => {
        console.log('Profile picture updated, refreshing images...');
        
        document.querySelectorAll('img[src*="profile_pictures"]').forEach(img => {
            if (img.src.includes('?')) {
                img.src = img.src.split('?')[0] + '?v=' + new Date().getTime();
            } else {
                img.src = img.src + '?v=' + new Date().getTime();
            }
        });
        
        // refresh the main sidebar profile picture
        const mainProfileImg = document.querySelector('.p-4.border-b img');
        if (mainProfileImg) {
            if (mainProfileImg.src.includes('?')) {
                mainProfileImg.src = mainProfileImg.src.split('?')[0] + '?v=' + new Date().getTime();
            } else {
                mainProfileImg.src = mainProfileImg.src + '?v=' + new Date().getTime();
            }
        }
    });

    // Listen for groups update event
    Livewire.on('groupsUpdated', () => {
        console.log('Groups updated, refreshing sidebar...');
        
        // Reload the page to show the new group
        setTimeout(() => {
            window.location.reload();
        }, 500);
    });

    // Initialize
    if (window.innerWidth < 768) {
        showSidebar();
    } else {
        chatArea.classList.remove('hidden');
    }

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 768) {
            sidebar.classList.remove('hidden');
            chatArea.classList.remove('hidden');
        } else {
            const activeChat = document.getElementById('headerName').textContent !== 'Select a chat';
            if (activeChat) {
                showChatArea();
            } else {
                showSidebar();
            }
        }
    });
});
</script>
</body>
</html>