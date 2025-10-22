<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Events\MessageSent;
use Carbon\Carbon;

class ChatList extends Component
{
    use WithFileUploads;

    public $users;
    public $conversationId = null;
    public $activeUser = null;
    public $messages;
    public $newMessage = '';
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
        'openConversation',
        'messageReceived' => 'handleBroadcastedMessage',
        'voteOnPoll'
    ];

    public function mount()
    {
        $this->users = User::where('id', '!=', Auth::id())->orderBy('name')->get();
        $this->messages = collect();
    }

    public function openConversation($userId)
    {
        $me = Auth::id();
        $otherId = (int) $userId;

        $conversation = Conversation::where(function($q) use ($me, $otherId) {
            $q->where('user_one_id', $me)->where('user_two_id', $otherId);
        })->orWhere(function($q) use ($me, $otherId) {
            $q->where('user_one_id', $otherId)->where('user_two_id', $me);
        })->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'user_one_id' => $me,
                'user_two_id' => $otherId
            ]);
        }

        $this->conversationId = $conversation->id;
        $this->activeUser = User::find($otherId);
        $this->loadMessages();

        Message::where('conversation_id', $this->conversationId)
            ->where('sender_id', '!=', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $this->dispatch('listenOnConversation', conversationId: $this->conversationId);
        $this->dispatch('messagesLoaded');
    }

    protected function loadMessages()
    {
        if (!$this->conversationId) {
            $this->messages = collect();
            return;
        }

        $this->messages = Message::where('conversation_id', $this->conversationId)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function sendMessage()
    {
        if ($this->uploadedFile) {
            $this->sendFile();
            return;
        }

        if (trim($this->newMessage) === '' || !$this->conversationId) {
            return;
        }

        try {
            $message = Message::create([
                'conversation_id' => $this->conversationId,
                'sender_id' => Auth::id(),
                'message' => trim($this->newMessage),
                'type' => 'text',
            ]);

            $this->processMessage($message);

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to send message: ' . $e->getMessage());
        }
    }

    public function sendFile()
    {
        if (!$this->uploadedFile || !$this->conversationId) return;

        $this->isUploading = true;

        try {
            $originalName = $this->uploadedFile->getClientOriginalName();
            $fileExtension = $this->uploadedFile->getClientOriginalExtension();
            $uniqueFileName = uniqid() . '_' . time() . '.' . $fileExtension;
            $filePath = $this->uploadedFile->storeAs('chat_files', $uniqueFileName, 'public');
            
            $mimeType = $this->uploadedFile->getMimeType();
            $type = $this->getFileType($mimeType);

            $messageData = [
                'conversation_id' => $this->conversationId,
                'sender_id' => Auth::id(),
                'message' => $filePath,
                'type' => $type,
                'file_path' => $filePath,
                'file_name' => $originalName,
                'file_size' => $this->uploadedFile->getSize(),
                'mime_type' => $mimeType,
            ];

            if (in_array($type, ['video', 'audio'])) {
                $messageData['duration'] = 0;
            }

            if ($type === 'video') {
                $messageData['thumbnail_path'] = $this->generateVideoThumbnail($filePath);
            }

            $message = Message::create($messageData);
            $this->processMessage($message);
            $this->uploadedFile = null;

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to upload file: ' . $e->getMessage());
        } finally {
            $this->isUploading = false;
        }
    }

    public function createPoll()
    {
        $errors = $this->validatePollManually();
        
        if (!empty($errors)) {
            session()->flash('error', implode(' ', $errors));
            return;
        }

        try {
            $pollOptionsArray = (array) $this->pollOptions;
            $filteredOptions = array_values(array_filter(array_map('trim', $pollOptionsArray), 'strlen'));

            $message = Message::create([
                'conversation_id' => $this->conversationId,
                'sender_id' => Auth::id(),
                'message' => 'Poll: ' . $this->pollQuestion,
                'type' => 'poll',
                'poll_question' => trim($this->pollQuestion),
                'poll_options' => $filteredOptions,
                'poll_votes' => array_fill(0, count($filteredOptions), 0),
            ]);

            $message->load('sender');
            $this->processMessage($message);
            $this->resetPoll();

            session()->flash('success', 'Poll created successfully!');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create poll: ' . $e->getMessage());
        }
    }

    private function validatePollManually()
    {
        $errors = [];

        // Validate poll question
        if (empty(trim($this->pollQuestion))) {
            $errors[] = 'Poll question is required.';
        }

        // Validate poll options
        $pollOptionsArray = (array) $this->pollOptions;
        $validOptions = 0;
        
        foreach ($pollOptionsArray as $option) {
            if (!empty(trim($option))) {
                $validOptions++;
            }
        }

        if ($validOptions < 2) {
            $errors[] = 'At least 2 poll options are required.';
        }

        return $errors;
    }

    public function shareContact()
    {
        // Manual validation for contact
        $errors = [];
        
        if (empty(trim($this->contactName))) {
            $errors[] = 'Contact name is required.';
        }
        
        if (empty(trim($this->contactPhone))) {
            $errors[] = 'Contact phone is required.';
        }
        
        if (!empty($errors)) {
            session()->flash('error', implode(' ', $errors));
            return;
        }

        try {
            $message = Message::create([
                'conversation_id' => $this->conversationId,
                'sender_id' => Auth::id(),
                'message' => 'Contact: ' . $this->contactName,
                'type' => 'contact',
                'contact_data' => [
                    'name' => $this->contactName,
                    'phone' => $this->contactPhone,
                    'email' => $this->contactEmail
                ]
            ]);

            $this->processMessage($message);
            $this->resetContact();

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to share contact: ' . $e->getMessage());
        }
    }

    public function voteOnPoll($messageId, $optionIndex)
    {
        $message = Message::find($messageId);
        
        if ($message && $message->type === 'poll') {
            $this->handlePollVote($message, (int)$optionIndex);
            $this->loadMessages();
        }
    }

    // Helper methods
    private function processMessage($message)
    {
        $message->load('sender');
        
        if ($this->activeUser && $this->activeUser->id != Auth::id()) {
            broadcast(new MessageSent($message))->toOthers();
        }
        
        $this->messages = $this->messages->push($message);
        $this->newMessage = '';
        $this->dispatch('messageAppended');
    }

    private function getFileType($mimeType)
    {
        if (str_contains($mimeType, 'image')) return 'image';
        if (str_contains($mimeType, 'video')) return 'video';
        if (str_contains($mimeType, 'audio')) return 'audio';
        return 'file';
    }

    private function generateVideoThumbnail($videoPath)
{
    try {
        // Check if FFmpeg is available
        if (!class_exists('FFMpeg\FFMpeg')) {
            \Log::warning('FFMpeg class not found - thumbnails disabled');
            return null;
        }

        // Create FFmpeg instance
        $ffmpeg = \FFMpeg\FFMpeg::create([
            'ffmpeg.binaries'  => env('FFMPEG_BINARIES', 'ffmpeg'),
            'ffprobe.binaries' => env('FFPROBE_BINARIES', 'ffprobe'),
            'timeout'          => 60, // 60 second timeout
            'ffmpeg.threads'   => 4,  // Number of threads
        ]);

        // Full path to the video file
        $fullVideoPath = Storage::disk('public')->path($videoPath);
        
        // Check if video file exists
        if (!file_exists($fullVideoPath)) {
            \Log::error('Video file not found: ' . $fullVideoPath);
            return null;
        }

        // Open the video file
        $video = $ffmpeg->open($fullVideoPath);
        
        // Generate thumbnail filename
        $thumbnailFileName = pathinfo($videoPath, PATHINFO_FILENAME) . '_thumb.jpg';
        $thumbnailPath = 'chat_thumbnails/' . $thumbnailFileName;
        
        // Create thumbnails directory if it doesn't exist
        Storage::disk('public')->makeDirectory('chat_thumbnails');
        
        // Get video duration to extract frame from 10% of the video
        $videoStream = $video->getStreams()->videos()->first();
        if ($videoStream) {
            $duration = $videoStream->get('duration');
            $frameTime = $duration ? min(5, $duration * 0.1) : 1;
        } else {
            $frameTime = 1;
        }
        
        // Extract frame and save as thumbnail
        $video
            ->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds($frameTime))
            ->save(Storage::disk('public')->path($thumbnailPath));
        
        \Log::info('Video thumbnail generated: ' . $thumbnailPath);
        return $thumbnailPath;

    } catch (\Exception $e) {
        \Log::error('Video thumbnail generation failed: ' . $e->getMessage());
        return null;
    }
}

    private function handlePollVote($message, $optionIndex)
    {
        $pollVotes = $message->poll_votes ?? [];
        
        if (isset($pollVotes[$optionIndex])) {
            $pollVotes[$optionIndex]++;
        }
        
        $message->update([
            'poll_votes' => $pollVotes
        ]);
        
        session()->flash('success', 'Vote submitted!');
    }

    public function addPollOption()
    {
        $this->pollOptions[] = '';
    }

    public function removePollOption($index)
    {
        if (count($this->pollOptions) > 2) {
            unset($this->pollOptions[$index]);
            $this->pollOptions = array_values($this->pollOptions);
        }
    }

    public function resetPoll()
    {
        $this->showPollModal = false;
        $this->pollQuestion = '';
        $this->pollOptions = ['', ''];
        $this->dispatch('poll-created');
    }

    public function resetContact()
    {
        $this->showContactModal = false;
        $this->contactName = '';
        $this->contactPhone = '';
        $this->contactEmail = '';
    }

    public function handleBroadcastedMessage()
    {
        $event = request('event') ?? request()->all();
        
        if (isset($event['conversation_id']) && $event['sender_id'] != Auth::id()) {
            if ($event['conversation_id'] == $this->conversationId) {
                $messageData = [
                    'id' => $event['id'],
                    'conversation_id' => $event['conversation_id'],
                    'sender_id' => $event['sender_id'],
                    'message' => $event['message'],
                    'type' => $event['type'] ?? 'text',
                    'created_at' => $event['created_at'],
                    'is_read' => true,
                ];

                if (($event['type'] ?? 'text') === 'file') {
                    $messageData['file_path'] = $event['file_path'];
                    $messageData['file_name'] = $event['file_name'];
                    $messageData['file_size'] = $event['file_size'];
                    $messageData['mime_type'] = $event['mime_type'];
                }

                $message = new Message($messageData);
                $message->setRelation('sender', (object) $event['sender']);
                
                $this->messages = $this->messages->push($message);
                
                $this->dispatch('messageAppended');
            }
        }
    }

    public function downloadFile($messageId)
    {
        $message = Message::findOrFail($messageId);
        
        if ($message->type === 'file' && Storage::disk('public')->exists($message->file_path)) {
            return Storage::disk('public')->download($message->file_path, $message->file_name);
        }
        
        session()->flash('error', 'File not found.');
        return null;
    }

    // Helper method to format file size
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

    // Helper method to get file icon based on type
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

    // Helper method to format dates
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

    // Helper method to get message status icons
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

    // Helper method to group messages by date
    public function getGroupedMessages()
    {
        if (!$this->messages || $this->messages->isEmpty()) {
            return collect();
        }

        return $this->messages->groupBy(function($message) {
            return Carbon::parse($message->created_at)->format('Y-m-d');
        });
    }

    public function render()
    {
        return view('livewire.chat-list', [
            'groupedMessages' => $this->getGroupedMessages(),
        ]);
    }
}