<div>
    <!-- Profile Picture Button -->
    <button wire:click="showProfileModal" class="flex items-center gap-3 hover:bg-gray-100 p-2 rounded-lg transition w-full">
        @auth
            @if(Auth::user()->profile_picture)
                <img src="{{ Storage::url(Auth::user()->profile_picture) }}" 
                     alt="Profile" 
                     class="w-10 h-10 rounded-full object-cover border-2 border-green-500">
            @else
                <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-semibold text-lg">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
            @endif
            
            <div class="text-left flex-1">
                <div class="font-medium text-gray-900">{{ Auth::user()->name }}</div>
                <div class="flex items-center gap-1 text-xs text-gray-500">
                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                    <span>Online</span>
                </div>
            </div>
        @endauth
    </button>

    <!-- Profile Modal -->
    @if($showModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg p-6 w-96 max-w-full">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Update Profile Picture</h3>
                <button wire:click="closeProfileModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Current Profile Picture -->
            <div class="flex justify-center mb-6">
                @if(Auth::user()->profile_picture)
                    <img src="{{ Storage::url(Auth::user()->profile_picture) }}" 
                         alt="Current Profile" 
                         class="w-32 h-32 rounded-full object-cover border-4 border-green-500">
                @else
                    <div class="w-32 h-32 bg-green-500 rounded-full flex items-center justify-center text-white font-semibold text-4xl">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                @endif
            </div>

            <!-- Upload New Picture -->
            <div class="space-y-4">
                <label class="block">
                    <span class="text-sm font-medium text-gray-700 mb-2 block">Upload New Picture</span>
                    <input type="file" 
                           wire:model="profilePicture"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100"
                           accept="image/*">
                </label>

                @error('profilePicture')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror

                @if($profilePicture)
                    <div class="text-center">
                        <p class="text-sm text-gray-600">Preview:</p>
                        <img src="{{ $profilePicture->temporaryUrl() }}" 
                             alt="Preview" 
                             class="w-20 h-20 rounded-full object-cover mx-auto mt-2 border">
                    </div>
                @endif

                <!-- Actions -->
                <div class="flex gap-2 pt-4">
                    @if(Auth::user()->profile_picture)
                        <button wire:click="removeProfilePicture" 
                                wire:confirm="Are you sure you want to remove your profile picture?"
                                class="flex-1 px-4 py-2 border border-red-500 text-red-500 rounded-lg hover:bg-red-50 transition">
                            Remove Picture
                        </button>
                    @endif
                    <button wire:click="closeProfileModal" 
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded z-50">
            {{ session('error') }}
        </div>
    @endif
</div>