<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_picture',
        'last_seen'
    ];

    /**
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_seen' => 'datetime',
        ];
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function conversationsAsUserOne(): HasMany
    {
        return $this->hasMany(Conversation::class, 'user_one_id');
    }

    public function conversationsAsUserTwo(): HasMany
    {
        return $this->hasMany(Conversation::class, 'user_two_id');
    }

    public function isOnline()
    {
        $lastSeen = $this->last_seen;
        if (is_string($lastSeen)) {
            $lastSeen = \Carbon\Carbon::parse($lastSeen);
        }
        return $this->last_seen && $this->last_seen->gt(now()->subMinutes(5));
    }

    public function getStatusColor()
    {
        return $this->isOnline() ? 'bg-green-500' : 'bg-gray-400';
    }

    public function getStatusText()
    {
        return $this->isOnline() ? 'Online' : 'Offline';
    }

    public function getInitial()
    {
        return strtoupper(substr($this->name, 0, 1));
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_members')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    public function groupMessages(): HasMany
    {
        return $this->hasMany(GroupMessage::class, 'sender_id');
    }

    public function sentFriendRequests(): HasMany
    {
        return $this->hasMany(Friend::class, 'user_id');
    }

    public function receivedFriendRequests(): HasMany
    {
        return $this->hasMany(Friend::class, 'friend_id');
    }

    public function friends()
    {
         return $this->sentFriendRequests()
            ->where('status', 'accepted')
            ->with('friend')
            ->get()
            ->merge(
                $this->receivedFriendRequests()
                    ->where('status', 'accepted')
                    ->with('user')
                    ->get()
            );
    }

    public function pendingFriendRequests()
    {
        return $this->receivedFriendRequests()
            ->where('status', 'pending')
            ->with('user')
            ->get();
    }

    public function isFriendsWith($userId): bool
    {
        return $this->sentFriendRequests()
            ->where('friend_id', $userId)
            ->where('status', 'accepted')
            ->exists()
            ||
            $this->receivedFriendRequests()
            ->where('user_id', $userId)
            ->where('status', 'accepted')
            ->exists();
             }


    public function hasPendingRequestFrom($userId): bool
    {
        return $this->receivedFriendRequests()
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->exists();
    }

    public function hasSentRequestTo($userId): bool
    {
        return $this->sentFriendRequests()
            ->where('friend_id', $userId)
            ->where('status', 'pending')
            ->exists();
    }
}