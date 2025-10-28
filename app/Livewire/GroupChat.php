<?php
// app/Livewire/GroupChat.php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GroupChat extends Component
{
    use WithFileUploads;

    public $groups;
    public $activeGroup = null;
    public $messages;
    public $newMessage = '';

    // Group creation
    public $showCreateGroupModal = false;
    public $groupName = '';
    public $groupDescription = '';
    public $selectedUsers = [];
    public $groupAvatar;

    // Group info
    public $showGroupInfoModal = false;
    public $showAddMembersModal = false;
    public $selectedNewMembers = [];

    protected $listeners = [
        'openGroupConversation',
        'showCreateGroupModal'
    ];

    public function mount()
    {
        $this->loadUserGroups();
        $this->messages = collect();
        $this->selectedUsers = [];
        $this->selectedNewMembers = [];
    }

    public function loadUserGroups()
    {
        $this->groups = Auth::user()->groups()->with(['members', 'creator'])->get();
    }

    public function showCreateGroupModal()
    {
        $this->showCreateGroupModal = true;
    }

    public function closeCreateGroupModal()
    {
        $this->showCreateGroupModal = false;
        $this->resetGroupCreation();
    }

    public function createGroup()
    {
        $errors = [];
        
        if (empty(trim($this->groupName))) {
            $errors[] = 'Group name is required.';
        } elseif (strlen(trim($this->groupName)) < 3) {
            $errors[] = 'Group name must be at least 3 characters.';
        }
        
        // Convert selectedUsers to array and check
        if ($this->selectedUsers instanceof \Illuminate\Support\Collection) {
            $selectedUsersArray = $this->selectedUsers->toArray();
        } else {
            $selectedUsersArray = (array)$this->selectedUsers;
        }
        
        $selectedUsersArray = array_filter($selectedUsersArray);
        
        if (count($selectedUsersArray) < 1) {
            $errors[] = 'Please select at least one member.';
        }
        
        if (!empty($errors)) {
            session()->flash('error', implode(' ', $errors));
            return;
        }

        try {
            // Create group
            $group = Group::create([
                'name' => trim($this->groupName),
                'description' => trim($this->groupDescription),
                'created_by' => Auth::id(),
            ]);

            // Add group avatar if uploaded
            if ($this->groupAvatar) {
                $path = $this->groupAvatar->store('group_avatars', 'public');
                $group->update(['avatar' => $path]);
            }

            // Add creator as admin
            $group->members()->attach(Auth::id(), ['role' => 'admin']);

            // Add selected members
            foreach ($selectedUsersArray as $userId) {
                $group->members()->attach((int)$userId, ['role' => 'member']);
            }

            $this->resetGroupCreation();
            $this->loadUserGroups();
            
            $this->dispatch('groupsUpdated');
            
            session()->flash('success', 'Group created successfully!');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create group: ' . $e->getMessage());
        }
    }

    private function resetGroupCreation()
    {
        $this->groupName = '';
        $this->groupDescription = '';
        $this->selectedUsers = [];
        $this->groupAvatar = null;
    }

    public function getUsersProperty()
    {
        return User::where('id', '!=', Auth::id())->orderBy('name')->get();
    }

    public function openGroupConversation($groupId)
    {
        $this->activeGroup = Group::with(['members', 'creator'])->find($groupId);
        
        if ($this->activeGroup && $this->activeGroup->isMember(Auth::id())) {
            $this->loadGroupMessages();
        }
    }

    protected function loadGroupMessages()
    {
        if (!$this->activeGroup) {
            $this->messages = collect();
            return;
        }

        $this->messages = GroupMessage::where('group_id', $this->activeGroup->id)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function sendGroupMessage()
    {
        if (trim($this->newMessage) === '' || !$this->activeGroup) {
            return;
        }

        try {
            $message = GroupMessage::create([
                'group_id' => $this->activeGroup->id,
                'sender_id' => Auth::id(),
                'message' => trim($this->newMessage),
                'type' => 'text',
            ]);

            $message->load('sender');
            $this->messages = $this->messages->push($message);
            $this->newMessage = '';

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to send message: ' . $e->getMessage());
        }
    }

    public function showGroupInfo()
    {
        if ($this->activeGroup) {
            $this->showGroupInfoModal = true;
        }
    }

    public function closeGroupInfoModal()
    {
        $this->showGroupInfoModal = false;
        $this->groupAvatar = null;
    }

    public function updateGroupAvatar()
    {
        if (!$this->activeGroup || !$this->groupAvatar) return;

        // Manual validation
        $errors = [];
        
        // Check if file is an image
        $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        if (!in_array($this->groupAvatar->getMimeType(), $allowedMimes)) {
            $errors[] = 'Please select a valid image file (JPG, PNG, GIF).';
        }
        
        // Check file size (2MB = 2097152 bytes)
        if ($this->groupAvatar->getSize() > 2097152) {
            $errors[] = 'File size too large. Maximum size is 2MB.';
        }
        
        if (!empty($errors)) {
            session()->flash('error', implode(' ', $errors));
            return;
        }

        try {
            // Delete old avatar if exists
            if ($this->activeGroup->avatar && Storage::disk('public')->exists($this->activeGroup->avatar)) {
                Storage::disk('public')->delete($this->activeGroup->avatar);
            }

            // Store new avatar
            $path = $this->groupAvatar->store('group_avatars', 'public');
            $this->activeGroup->update(['avatar' => $path]);

            $this->groupAvatar = null;
            $this->activeGroup->refresh();
            
            session()->flash('success', 'Group photo updated successfully!');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update group photo: ' . $e->getMessage());
        }
    }

    public function showAddMembersModal()
    {
        $this->showAddMembersModal = true;
        $this->selectedNewMembers = [];
    }

    public function closeAddMembersModal()
    {
        $this->showAddMembersModal = false;
        $this->selectedNewMembers = [];
    }

    public function addMembersToGroup()
    {
        if (!$this->activeGroup) return;

        // Convert to array if it's a Collection
        if ($this->selectedNewMembers instanceof \Illuminate\Support\Collection) {
            $selectedMembers = $this->selectedNewMembers->toArray();
        } else {
            $selectedMembers = (array)$this->selectedNewMembers;
        }

        $selectedMembers = array_filter($selectedMembers);

        if (empty($selectedMembers)) {
            session()->flash('error', 'Please select at least one member to add.');
            return;
        }

        try {
            foreach ($selectedMembers as $userId) {
                if (!$this->activeGroup->isMember($userId)) {
                    $this->activeGroup->members()->attach($userId, ['role' => 'member']);
                }
            }

            $this->closeAddMembersModal();
            $this->activeGroup->load('members');
            session()->flash('success', 'Members added successfully!');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to add members: ' . $e->getMessage());
        }
    }

    public function getAvailableUsersProperty()
    {
        return User::where('id', '!=', Auth::id())
                   ->whereNotIn('id', $this->activeGroup?->members->pluck('id') ?? [])
                   ->orderBy('name')
                   ->get();
    }

    public function getOnlineMembersCountProperty()
    {
        if (!$this->activeGroup) return 0;
        
        return $this->activeGroup->members()
            ->where('last_seen', '>=', now()->subMinutes(5))
            ->count();
    }

    public function updatedSelectedNewMembers($value)
    {
        if (!is_array($this->selectedNewMembers)) {
            $this->selectedNewMembers = [];
        }
    }

    public function render()
    {
        return view('livewire.group-chat');
    }
}