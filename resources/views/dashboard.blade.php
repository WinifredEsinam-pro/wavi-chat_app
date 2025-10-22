<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Wavi Chat</title>
    <link rel="icon" type="image/png" href="{{ asset('image/wavi_logo.png') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        /* Mobile-first responsive styles */
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
    {{-- LEFT: Sidebar - Show on mobile by default --}}
    <aside id="sidebar" class="sidebar-mobile w-full md:w-[380px] bg-gray-50 border-r border-gray-300 flex flex-col md:static absolute inset-0 z-10 md:z-auto">
        {{-- Sidebar Header - Clean without unnecessary icons --}}
        <div class="p-4 border-b border-gray-300 flex items-center justify-between bg-white">
            <div class="flex items-center gap-3">
                {{-- User Avatar --}}
                <div class="relative">
                    <div class="w-10 h-10 bg-green-200 rounded-full flex items-center justify-center text-green-800 font-medium">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></div>
                </div>
                <span class="font-semibold text-gray-800">{{ auth()->user()->name }}</span>
            </div>

            {{-- Only keep useful actions - Status update icon --}}
            <div class="flex items-center gap-3 text-gray-500">
                <button class="hover:text-gray-700 transition" title="Update status">
                    <i class="fas fa-circle-dot text-lg"></i>
                </button>
            </div>
        </div>

        {{-- Simple Users List --}}
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

            {{-- All Users --}}
            @php
                $allUsers = \App\Models\User::where('id', '!=', auth()->id())->orderBy('name')->get();
            @endphp

            @if($allUsers->count() > 0)
                @foreach($allUsers as $u)
                    <div data-user-id="{{ $u->id }}" 
                         class="user-item cursor-pointer p-3 hover:bg-gray-100 border-b border-gray-200 flex items-center gap-3 transition-colors">
                        <div class="relative">
                            <div class="w-12 h-12 bg-green-200 rounded-full flex items-center justify-center text-green-800 font-medium text-lg">
                                {{ strtoupper(substr($u->name, 0, 1)) }}
                            </div>
                            <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-600 rounded-full border-2 border-white"></div>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-600">{{ $u->name }}</h3>
                            <p class="text-sm text-gray-600">Tap to start chatting</p>
                        </div>
                    </div>
                @endforeach
            @else
                {{-- No users message --}}
                <div class="text-center text-gray-500 py-8">
                    <i class="fas fa-users text-3xl mb-2 opacity-50"></i>
                    <div class="text-sm">No other users found</div>
                </div>
            @endif
        </div>
    </aside>

    {{-- RIGHT: Chat Area - Hidden on mobile by default --}}
    <main id="chatArea" class="chat-area-mobile flex-1 bg-white flex flex-col md:static absolute inset-0 z-10 md:z-auto hidden md:flex">
        {{-- Chat Header with Back Button --}}
        <div class="h-16 bg-green-700 text-white flex items-center justify-between px-4">
            <div class="flex items-center gap-3">
                {{-- Mobile Back Button - Always show on mobile --}}
                <button id="backBtn" class="text-white mr-2 md:hidden">
                    <i class="fas fa-arrow-left text-lg"></i>
                </button>
                <div class="relative">
                    <div id="headerAvatar" class="w-10 h-10 bg-green-200 rounded-full flex items-center justify-center text-green-800 font-medium">
                        W
                    </div>
                    <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></div>
                </div>
                <div>
                    <div id="headerName" class="font-semibold text-lg">Select a chat</div>
                    <div id="headerSub" class="text-sm opacity-80">online</div>
                </div>
            </div>

            {{-- Header Icons - Show only when chat is active --}}
            <div id="headerIcons" class="flex items-center gap-4 text-white hidden md:flex">
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
        <div id="chatWindow" class="flex-1 overflow-y-auto">
            @livewire('chat-list')
        </div>
    </main>
</div>

@livewireScripts

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const chatArea = document.getElementById('chatArea');
    const backBtn = document.getElementById('backBtn');
    const userItems = document.querySelectorAll('.user-item');
    const headerIcons = document.getElementById('headerIcons');

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

    // User item click handler
    userItems.forEach(item => {
        item.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            const userName = this.querySelector('h3').textContent;
            const userInitial = userName.charAt(0).toUpperCase();

            // Update header
            document.getElementById('headerName').textContent = userName;
            document.getElementById('headerAvatar').textContent = userInitial;
            
            // Show header icons on mobile when chat is selected
            if (window.innerWidth < 768) {
                headerIcons.classList.remove('hidden');
            }

            // Show chat area on mobile
            showChatArea();

            // Call Livewire
            if (window.Livewire) {
                Livewire.dispatch('openConversation', { userId: userId });
            }
        });
    });

    // Mobile back button - show sidebar
    if (backBtn) {
        backBtn.addEventListener('click', function() {
            showSidebar();
            // Reset header
            document.getElementById('headerName').textContent = 'Select a chat';
            document.getElementById('headerAvatar').textContent = 'W';
            // Hide header icons on mobile
            if (window.innerWidth < 768) {
                headerIcons.classList.add('hidden');
            }
        });
    }

    // Event listeners for chat functionality
    document.addEventListener('listenOnConversation', function(e) {
        const convId = e.detail.conversationId;
        if (convId && window.listenToConversation) {
            try { window.listenToConversation(convId); } catch(err) { console.warn(err); }
        }
    });

    document.addEventListener('messagesLoaded', () => {
        const cw = document.getElementById('chatWindow');
        if (cw) setTimeout(() => cw.scrollTop = cw.scrollHeight, 100);
    });
    
    document.addEventListener('messageAppended', () => {
        const cw = document.getElementById('chatWindow');
        if (cw) setTimeout(() => cw.scrollTop = cw.scrollHeight, 100);
    });

    // Initialize - Show sidebar on mobile, chat area on desktop
    if (window.innerWidth < 768) {
        showSidebar();
    } else {
        chatArea.classList.remove('hidden');
    }

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 768) {
            // Desktop - show both
            sidebar.classList.remove('hidden');
            chatArea.classList.remove('hidden');
        } else {
            // Mobile - show only sidebar if no chat is active
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