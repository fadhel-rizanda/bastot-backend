<?php

namespace App\Models\chat;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chat extends Model
{
    use SoftDeletes;
    protected $table = 'chats';
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
        'name',
        'description',
        'avatar',
        'channel_name',
        'type',
        'created_by',
        'settings',
        'is_active',
        'last_activity_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'last_activity_at' => 'datetime',
    ];

    public function creator(){
        return $this->belongsTo(User::class, 'created_by');
    }

    public function chatUsers(){
        return $this->hasMany(ChatUser::class, 'chat_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'chat_users')
            ->using(ChatUser::class)  // pengganti 'chat_id', 'user_id')
            ->withPivot(['role_id', 'nickname', 'is_muted', 'is_blocked', 'is_active', 'joined_at', 'left_at'])
            ->withTimestamps();
    }

    public function activeUsers()
    {
        return $this->users()->wherePivot('is_active', true); // hanya bisa untuk many to many
    }

    public function messages(){
        return $this->hasManyThrough(ChatUserMessage::class, ChatUser::class);
    }

    public function lastMessage()
    {
        return $this->messages()
            ->latest()
            ->first();
    }

    public function addUser(User $user, $roleId = null, $nickname = null)
    {
        return $this->chatUsers()->create([
            'user_id' => $user->id,
            'role_id' => $roleId,
            'nickname' => $nickname,
            'is_active' => true,
            'joined_at' => now(),
        ]);
    }

    public function removeUser(User $user): bool
    {
        return $this->chatUsers()
            ->where('user_id', $user->id)
            ->update([
                'is_active' => false,
                'left_at' => now(),
            ]);
    }

    public function updateLastActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
    public function scopeForUser($query, $userId)
    {
        return $query->whereHas('chatUsers', function ($q) use ($userId) {
            $q->where('user_id', $userId)->where('is_active', true);
        });
    }
}
