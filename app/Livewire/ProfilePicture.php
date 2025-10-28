<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfilePicture extends Component
{
    use WithFileUploads;

    public $profilePicture;
    public $showModal = false;

    public function showProfileModal()
    {
        $this->showModal = true;
    }

    public function closeProfileModal()
    {
        $this->showModal = false;
        $this->profilePicture = null;
    }

    public function updatedProfilePicture()
    {
        $this->validate([
            'profilePicture' => 'image|max:2048',
        ]);

        try {
            $user = Auth::user();
            
            // Delete old profile picture if exists
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            // Store new picture
            $path = $this->profilePicture->store('profile_pictures', 'public');
            
            // Update user
            $user->update(['profile_picture' => $path]);
            
            $this->profilePicture = null;
            $this->showModal = false;
            
            session()->flash('success', 'Profile picture updated successfully!');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to upload profile picture: ' . $e->getMessage());
        }
    }

    public function removeProfilePicture()
    {
        $user = Auth::user();
        if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
            Storage::disk('public')->delete($user->profile_picture);
        }
        
        $user->update(['profile_picture' => null]);
        $this->showModal = false;
        session()->flash('success', 'Profile picture removed!');
    }

    public function render()
    {
        return view('livewire.profile-picture');
    }
}