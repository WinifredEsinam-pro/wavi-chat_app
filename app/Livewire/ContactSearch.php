<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ContactSearch extends Component
{
    public $showContactSearch = false;
    public $searchQuery = '';
    public $searchResults = [];

    public function openContactSearch()
    {
        $this->showContactSearch = true;
        $this->searchQuery = '';
        $this->searchResults = [];
    }

    public function closeContactSearch()
    {
        $this->showContactSearch = false;
        $this->searchQuery = '';
        $this->searchResults = [];
    }

    public function updatedSearchQuery()
    {
        $this->performSearch();
    }

    public function performSearch()
    {
        // If search query is empty, clear results
        if (empty(trim($this->searchQuery))) {
            $this->searchResults = [];
            return;
        }

        $searchTerm = trim($this->searchQuery);
        
        try {
            // Search Users
            $users = User::where('id', '!=', Auth::id())
                ->where(function($query) use ($searchTerm) {
                    $query->where('name', 'like', '%' . $searchTerm . '%')
                          ->orWhere('email', 'like', '%' . $searchTerm . '%');
                })
                ->get()
                ->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'avatar' => $user->profile_picture ? Storage::url($user->profile_picture) : null,
                        'type' => 'user',
                    ];
                });

            // Search Groups
            $groups = Group::where(function($query) use ($searchTerm) {
                    $query->where('name', 'like', '%' . $searchTerm . '%')
                          ->orWhere('description', 'like', '%' . $searchTerm . '%');
                })
                ->whereHas('members', function($query) {
                    $query->where('user_id', Auth::id());
                })
                ->get()
                ->map(function($group) {
                    return [
                        'id' => $group->id,
                        'name' => $group->name,
                        'description' => $group->description,
                        'avatar' => $group->avatar ? Storage::url($group->avatar) : null,
                        'members_count' => $group->members->count(),
                        'type' => 'group',
                    ];
                });

            // Combine results
            $this->searchResults = array_merge($users->toArray(), $groups->toArray());

        } catch (\Exception $e) {
            \Log::error('Search error: ' . $e->getMessage());
            $this->searchResults = [];
        }
    }

    public function startChat($result)
    {
        if ($result['type'] === 'user') {
            $this->dispatch('startIndividualChat', $result['id']);
        } elseif ($result['type'] === 'group') {
            $this->dispatch('openGroupChat', $result['id']);
        }
        
        $this->closeContactSearch();
    }

    public function render()
    {
        return view('livewire.contact-search');
    }
}