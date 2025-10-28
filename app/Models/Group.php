<?php
// app/Models/Group.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    protected $fillable = [
        'name', 
        'description', 
        'created_by', 
        'avatar'
    ];

    // Group creator
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Group members (many-to-many through group_members table)
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_members')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    // Group messages
    public function messages(): HasMany
    {
        return $this->hasMany(GroupMessage::class);
    }

    // Check if user is a member of this group
    public function isMember($userId): bool
    {
        return $this->members()->where('user_id', $userId)->exists();
    }

    // Check if user is admin of this group
    public function isAdmin($userId): bool
    {
        return $this->members()
                    ->where('user_id', $userId)
                    ->where('role', 'admin')
                    ->exists();
    }

   public function getOnlineMembersCountAttribute(): int
{
    return $this->members()
        ->where('last_seen', '>=', now()->subMinutes(5))
        ->count();
}
}