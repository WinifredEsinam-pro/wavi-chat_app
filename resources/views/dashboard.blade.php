<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Wavi Chat</title>
    <link rel="icon" type="image/png" href="{{ asset('image/wavi_logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-white text-gray-900 antialiased">

<div class="flex h-screen">
    {{-- LEFT: Sidebar - Hidden on mobile when chat is open --}}
    <aside id="sidebar" class="w-full md:w-[25%] md:max-w-sm bg-gray-100 border-r border-gray-300 flex flex-col transition-transform duration-300 md:transform-none fixed md:relative z-10">
        <div class="p-4 border-b border-gray-300 flex items-center justify-between bg-white">
            <div class="flex items-center gap-3">
                <img src="{{ asset('image/wavi_logo1.png') }}" alt="Wavi Logo" class="w-12 h-12 md:w-14 md:h-14"/>
            
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-red-600 hover:underline">Logout</button>
            </form>
        </div>

        <div class="p-4 flex-1 overflow-hidden">
            <h2 class="text-lg font-semibold mb-4">Chats</h2>

            @php
                use App\Models\User;
                $allUsers = User::orderBy('name')->get();
            @endphp

            <ul id="conversationsList" class="space-y-2 overflow-y-auto max-h-[calc(100vh-180px)]">
                @foreach($allUsers as $u)
                    <li data-user-id="{{ $u->id }}" data-user-name="{{ $u->name }}"
                        class="cursor-pointer p-3 rounded-md hover:bg-gray-200 flex items-center gap-3 user-item transition-colors duration-200">
                        <div class="w-10 h-10 bg-green-200 rounded-full flex items-center justify-center text-green-800 font-medium">
                            {{ strtoupper(substr($u->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-gray-900 truncate">{{ $u->name }}</div>
                            <div class="text-sm text-gray-500 truncate">
                                @if(auth()->id() === $u->id) You @else Tap to chat @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </aside>

    {{-- RIGHT: Chat area --}}
    <main class="flex-1 bg-white flex flex-col md:static relative">
        {{-- Mobile header --}}
        <div id="chatHeader" class="h-16 bg-green-700 text-white flex items-center px-4 md:px-6">
            <button id="backBtn" class="md:hidden text-white mr-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            <div class="flex items-center gap-3">
                <div id="headerAvatar" class="w-10 h-10 bg-green-200 rounded-full flex items-center justify-center text-green-800 font-medium">W</div>
                <div>
                    <div id="headerName" class="font-semibold text-lg">Select a chat</div>
                    <div id="headerSub" class="text-sm opacity-80">Online</div>
                </div>
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
    const backBtn = document.getElementById('backBtn');
    const userList = document.getElementById('conversationsList');

    function showChatOnMobile() {
        sidebar.classList.add('-translate-x-full');
        sidebar.classList.remove('translate-x-0');
    }

    function showSidebarOnMobile() {
        sidebar.classList.remove('-translate-x-full');
        sidebar.classList.add('translate-x-0');
    }

    // User list click handler
    if (userList) {
        userList.addEventListener('click', function(e) {
            let item = e.target.closest('.user-item');
            if (!item) return;

            const userId = item.getAttribute('data-user-id');
            const userName = item.getAttribute('data-user-name') || 'Chat';

            // Update header
            const headerName = document.getElementById('headerName');
            const headerAvatar = document.getElementById('headerAvatar');
            if (headerName) headerName.innerText = userName;
            if (headerAvatar) headerAvatar.innerText = userName.charAt(0).toUpperCase();

            // Mobile: hide sidebar
            if (window.innerWidth < 768) showChatOnMobile();

            // Call Livewire
            if (window.Livewire) {
                Livewire.dispatch('openConversation', { userId: userId });
            }
        });
    }

    // Mobile back button
    if (backBtn) {
        backBtn.addEventListener('click', function() {
            showSidebarOnMobile();
            // Reset header
            const headerName = document.getElementById('headerName');
            const headerAvatar = document.getElementById('headerAvatar');
            if (headerName) headerName.innerText = 'Select a chat';
            if (headerAvatar) headerAvatar.innerText = 'W';
        });
    }

    // Echo event listeners
    document.addEventListener('listenOnConversation', function(e) {
        const convId = e.detail.conversationId;
        if (convId && window.listenToConversation) {
            try { window.listenToConversation(convId); } catch(err) { console.warn(err); }
        }
    });

    // Scroll handlers
    document.addEventListener('messagesLoaded', () => {
        const cw = document.getElementById('chatWindow');
        if (cw) setTimeout(() => cw.scrollTop = cw.scrollHeight, 100);
    });
    
    document.addEventListener('messageAppended', () => {
        const cw = document.getElementById('chatWindow');
        if (cw) setTimeout(() => cw.scrollTop = cw.scrollHeight, 100);
    });

    // Initialize sidebar state
    if (window.innerWidth >= 768) {
        showSidebarOnMobile();
    }
});
</script>

</body>
</html>