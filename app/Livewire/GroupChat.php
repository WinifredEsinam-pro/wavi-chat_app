<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class GroupChat extends Component
{
    use WithFileUploads;

    public $groups;
    public $activeGroup = null;
    public $messages;
    public $newMessage = '';

    public $showCreateGroupModal = false;
    public $groupName = '';
    public $groupDescription = '';
    public $selectedUsers = [];
    public $groupAvatar;

    public $showGroupInfoModal = false;
    public $showAddMembersModal = false;
    public $selectedNewMembers = [];

    public $uploadedFile;
    public $isUploading = false;
    public $showPollModal = false;
    public $pollQuestion = '';
    public $pollOptions = ['', ''];
    
    public $showContactModal = false;
    public $contactName = '';
    public $contactPhone = '';
    public $contactEmail = '';

    protected $listeners = [
        'openGroupConversation',
        'showCreateGroupModal',
        'messageReceived' => 'handleBroadcastedMessage',
        'voteOnPoll',
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
            $group = Group::create([
                'name' => trim($this->groupName),
                'description' => trim($this->groupDescription),
                'created_by' => Auth::id(),
            ]);

            if ($this->groupAvatar) {
                $path = $this->groupAvatar->store('group_avatars', 'public');
                $group->update(['avatar' => $path]);
            }

            $group->members()->attach(Auth::id(), ['role' => 'admin']);

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

        $errors = [];
        
       $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        if (!in_array($this->groupAvatar->getMimeType(), $allowedMimes)) {
            $errors[] = 'Please select a valid image file (JPG, PNG, GIF).';
        }
        
        if ($this->groupAvatar->getSize() > 2097152) {
            $errors[] = 'File size too large. Maximum size is 2MB.';
        }
        
        if (!empty($errors)) {
            session()->flash('error', implode(' ', $errors));
            return;
        }

        try {
            if ($this->activeGroup->avatar && Storage::disk('public')->exists($this->activeGroup->avatar)) {
                Storage::disk('public')->delete($this->activeGroup->avatar);
            }

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

    public function formatMessageDate($date)
    {
        $messageDate = Carbon::parse($date);
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        if ($messageDate->isToday()) {
            return 'Today';
        } elseif ($messageDate->isYesterday()) {
            return 'Yesterday';
        } elseif ($messageDate->isCurrentWeek()) {
            return $messageDate->format('l'); 
        } else {
            return $messageDate->format('M j, Y');
        }
    }

    public function getMessageStatus($message)
    {
        if ($message->sender_id != Auth::id()) {
            return '';
        }

        if ($message->is_read) {
            return 'fa-check-double text-blue-700';
        } else {
            return 'fa-check text-gray-600';
        }
    }
    public function getGroupedMessages()
    {
        if (!$this->messages || $this->messages->isEmpty()) {
            return collect();
        }

        return $this->messages->groupBy(function($message) {
            return Carbon::parse($message->created_at)->format('Y-m-d');
        });
    }

    public function getFileIcon($mimeType, $fileName)
    {
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        
        if (str_contains($mimeType, 'image')) {
            return 'fa-image';
        } elseif (str_contains($mimeType, 'pdf')) {
            return 'fa-file-pdf';
        } elseif (str_contains($mimeType, 'word') || in_array($extension, ['doc', 'docx'])) {
            return 'fa-file-word';
        } elseif (str_contains($mimeType, 'excel') || in_array($extension, ['xls', 'xlsx'])) {
            return 'fa-file-excel';
        } elseif (str_contains($mimeType, 'zip') || in_array($extension, ['zip', 'rar'])) {
            return 'fa-file-archive';
        } else {
            return 'fa-file';
        }
    }

    public function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }



    

    public function render()
    {
        return view('livewire.group-chat', [
            'groupedMessages' => $this->getGroupedMessages(),
        ]);
    }
}