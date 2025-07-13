<?php

namespace App\Models\chat;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ChatUser extends Model
{
    protected $table = 'chat_users';
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
        'chat_id',
        'user_id',
        'role_id',
        'nickname',
        'is_muted',
        'is_blocked',
        'is_active',
        'joined_at',
        'left_at',
        'last_read_message_id',
    ];

    protected $casts = [
        'is_muted' => 'boolean',
        'is_blocked' => 'boolean',
        'is_active' => 'boolean',
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class, 'chat_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function messages()
    {
        return $this->hasMany(ChatUserMessage::class, 'chat_user_id');
    }

    public function lastReadMessage()
    {
        return $this->belongsTo(ChatUserMessage::class, 'last_read_message_id');
    }

    public function sendMessage(string $message, string $type = 'text', array $metadata = [])
    {
        $data = [
            'user_id' => $this->user_id,
            'chat_id' => $this->chat_id,
            'message' => $message,
            'type' => $type,
            'metadata' => $metadata,
        ];
        $chatMessage = $this->messages()->create($data);
        $this->chat()->updateLastActivity();

        return $chatMessage;
    }

    public function markAsRead(ChatUserMessage $message)
    {
        $this->update(['last_read_message_id' => $message->id]);
    }

    public function getUnreadMessageCount(): int
    {
        $lastReadMessageId = $this->last_read_message_id ?? 0;
        return $this->messages()
            ->where('id', '>', $lastReadMessageId)
            ->where('user_id', '!=', $this->user_id)
            ->count();
    }

    public function hasRole(string $roleName): bool
    {
        return $this->role && $this->role->name === $roleName;
    }

    public function isOwner(): bool
    {
        return $this->hasRole('OWNER');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('ADMIN') || $this->isOwner();
    }

    public function isMember(): bool
    {
        return $this->hasRole('MEMBER');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    public function scopeInChat($query, $chatId)
    {
        return $query->where('chat_id', $chatId);
    }
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
