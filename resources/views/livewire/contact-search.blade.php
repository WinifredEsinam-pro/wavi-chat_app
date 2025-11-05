<div>
    <!-- Search Button -->
    <button wire:click="openContactSearch" 
            class="flex items-center gap-3 p-3 hover:bg-gray-100 rounded-lg transition-colors w-full">
        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-600">
            <i class="fas fa-search"></i>
        </div>
        <span class="text-gray-700 font-medium">Search Contacts</span>
    </button>

    <!-- Contact Search Modal - WhatsApp Style -->
    @if($showContactSearch)
    <div class="fixed inset-0 z-50">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black bg-opacity-50" wire:click="closeContactSearch"></div>
        
        <!-- Modal Content -->
        <div class="absolute top-0 left-0 w-full md:w-[380px] h-full bg-white shadow-xl">
            <!-- Header -->
            <div class="bg-green-600 text-white p-4">
                <div class="flex items-center gap-4">
                    <button wire:click="closeContactSearch" class="text-white">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </button>
                    <div class="flex-1">
                        <h2 class="text-xl font-semibold">Search Contacts</h2>
                    </div>
                </div>
                
                <!-- Search Input -->
                <div class="mt-4 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input 
                        type="text" 
                        wire:model.live="searchQuery"
                        placeholder="Search contacts, users, or groups..."
                        class="w-full pl-10 pr-4 py-3 bg-white text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                        autofocus
                        wire:keydown.escape="closeContactSearch"
                    >
                </div>
            </div>

            <!-- Search Results -->
            <div class="flex-1 overflow-y-auto h-[calc(100vh-140px)]">
                @if($searchQuery === '')
                    <!-- When no search -->
                    <div class="text-center py-12">
                        <i class="fas fa-search text-4xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500">Search for contacts or groups</p>
                    </div>
                @else
                    <!-- When searching -->
                    @if(count($searchResults) > 0)
                        <div class="p-2 space-y-1">
                            @foreach($searchResults as $result)
                            <div class="flex items-center gap-3 p-3 hover:bg-gray-50 rounded-lg cursor-pointer transition-all duration-200"
                                 wire:click="startChat({{ json_encode($result) }})">
                                <div class="flex-shrink-0">
                                    @if($result['avatar'])
                                        <img src="{{ $result['avatar'] }}" 
                                             alt="{{ $result['name'] }}"
                                             class="w-12 h-12 rounded-full object-cover">
                                    @else
                                        <div class="w-12 h-12 {{ $result['type'] === 'group' ? 'bg-purple-500' : 'bg-blue-500' }} rounded-full flex items-center justify-center text-white font-semibold text-lg">
                                            {{ strtoupper(substr($result['name'], 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-900 truncate">{{ $result['name'] }}</p>
                                    <p class="text-sm text-gray-500 truncate">
                                        @if($result['type'] === 'group')
                                            {{ $result['members_count'] }} members • {{ $result['description'] ?? 'Group chat' }}
                                        @else
                                            {{ $result['email'] ?? 'User' }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <i class="fas fa-search text-4xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500 text-lg">No results found</p>
                            <p class="text-gray-400 text-sm mt-1">Try searching with different keywords</p>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
    @endif
</div>