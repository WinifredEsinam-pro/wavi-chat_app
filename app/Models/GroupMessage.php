<?php
// app/Models/GroupMessage.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupMessage extends Model
{
    protected $fillable = [
        'group_id',
        'sender_id', 
        'message',
        'type',
        'file_path',
        'file_name', 
        'file_size',
        'mime_type',
        'poll_question',
        'poll_options',
        'poll_votes',
        'contact_data'
    ];

    protected $casts = [
        'poll_question' => 'array',
        'poll_options' => 'array',
        'poll_votes' => 'array',
        'contact_data' => 'array',
    ];

    // Message sender
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Group this message belongs to
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    // Helper methods for message types
    public function getIsTextAttribute(): bool
    {
        return $this->type === 'text';
    }

    public function getIsImageAttribute(): bool
    {
        return $this->type === 'image';
    }

    public function getIsFileAttribute(): bool
    {
        return in_array($this->type, ['file', 'image', 'video', 'audio']);
    }

    public function getIsPollAttribute(): bool
    {
        return $this->type === 'poll';
    }

    public function getIsContactAttribute(): bool
    {
        return $this->type === 'contact';
    }
}