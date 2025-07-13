<?php

namespace App\Models\chat;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ChatUserMessage extends Model
{
    protected $table = 'chat_user_messages';
    public $incrementing = false;
    protected $keyType = 'string';
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) \Str::uuid();
            }
        });
    }
    protected $fillable = [
        'chat_user_id',
//        'user_id',    redundant, use chatUser relation
//        'chat_id',
        'parent_message_id',
        'message',
        'type',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'metadata',
        'edited_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'edited_at' => 'datetime',
    ];

    public function chatUser()
    {
        return $this->belongsTo(ChatUser::class, 'chat_user_id');
    }

    public function user()
    {
//        return $this->belongsTo(User::class, 'user_id');
        return $this->chatUser->user ?? null;
    }

    public function chat()
    {
//        return $this->belongsTo(Chat::class, 'chat_id');
        return $this->chatUser->chat ?? null;
    }

    public function parentMessage()
    {
        return $this->belongsTo(ChatUserMessage::class, 'parent_message_id');
    }

    public function replies()
    {
        return $this->hasMany(ChatUserMessage::class, 'parent_message_id');
    }

    public function messageStatus()
    {
        return $this->hasMany(ChatUserMessageStatus::class, 'chat_user_message_id');
    }

    public function editMessage(string $newMessage): void
    {
        $this->update([
            'message' => $newMessage,
            'edited_at' => now(),
        ]);
    }

    public function reply(string $message, string $type = 'text'): ChatUserMessage
    {
        return $this->chatUser->sendMessage($message, $type, [
            'parent_message_id' => $this->id,
        ]);
    }

//    helper
    public function isEdited(): bool
    {
        return !is_null($this->edited_at);
    }

    public function isReply(): bool
    {
        return !is_null($this->parent_message_id);
    }

    public function getFileSizeFormatted(): ?string
    {
        if (!$this->file_size) return null;

        $bytes = (int) $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    // Scopes
//    public function scopeInChat($query, $chatId)
//    {
//        return $query->where('chat_id', $chatId);
//    }
//    public function scopeByUser($query, $userId)
//    {
//        return $query->where('user_id', $userId);
//    }
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
    public function scopeReplies($query)
    {
        return $query->whereNotNull('parent_message_id');
    }
    public function scopeRootMessages($query)
    {
        return $query->whereNull('parent_message_id');
    }
    public function scopeSearch($query, $term)
    {
        return $query->where('message', 'ILIKE', "%{$term}%");
    }
}
