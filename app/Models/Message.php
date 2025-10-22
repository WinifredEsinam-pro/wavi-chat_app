<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'conversation_id', 
        'sender_id', 
        'message',
        'is_read',
        'type',
        'file_path',
        'file_name', 
        'file_size',
        'mime_type',
        'duration', 
        'thumbnail_path', 
        'contact_data', 
        'poll_question',
        'poll_options',
        'poll_votes' 
    ];

    protected $attributes = [
        'is_read' => false,
        'type' => 'text',
    ];

    protected $casts = [
        'contact_data' => 'array',
        'poll_options' => 'array',
        'poll_votes' => 'array',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Helper methods for different message types
    public function getIsFileAttribute()
    {
        return in_array($this->type, ['image', 'video', 'audio', 'file']);
    }

    public function getIsImageAttribute()
    {
        return $this->type === 'image';
    }

    public function getIsVideoAttribute()
    {
        return $this->type === 'video';
    }

    public function getIsAudioAttribute()
    {
        return $this->type === 'audio';
    }

    public function getIsContactAttribute()
    {
        return $this->type === 'contact';
    }

    public function getIsPollAttribute()
    {
        return $this->type === 'poll';
    }

    public function getIsDocumentAttribute()
    {
        return $this->type === 'file' && !$this->is_image && !$this->is_video && !$this->is_audio;
    }

    // Get file icon based on type
    public function getFileIconAttribute()
    {
        return match($this->type) {
            'image' => 'fa-image',
            'video' => 'fa-video',
            'audio' => 'fa-music',
            'file' => 'fa-file',
            'contact' => 'fa-user',
            'poll' => 'fa-chart-bar',
            default => 'fa-file'
        };
    }

    // Get contact information
    public function getContactInfoAttribute()
    {
        if ($this->is_contact && $this->contact_data) {
            return (object) $this->contact_data;
        }
        return null;
    }

    // Get poll information
    public function getPollInfoAttribute()
    {
        if ($this->is_poll) {
            return [
                'question' => $this->poll_question,
                'options' => $this->poll_options ?? [],
                'votes' => $this->poll_votes ?? [],
                'total_votes' => array_sum($this->poll_votes ?? [])
            ];
        }
        return null;
    }

    // Vote on a poll option
    public function vote($optionIndex, $userId)
    {
        if (!$this->is_poll) return false;

        $votes = $this->poll_votes ?? array_fill(0, count($this->poll_options), 0);
        
        $votes[$optionIndex] = ($votes[$optionIndex] ?? 0) + 1;
        
        $this->poll_votes = $votes;
        return $this->save();
    }
}