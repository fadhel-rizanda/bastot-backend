<?php

namespace App\Models\chat;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ChatUserMessageStatus extends Model
{
    protected $table = 'chat_user_message_status';
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
        'message_id',
        'user_id',
        'status',
        'status_at',
    ];

    protected $casts = [
        'status_at' => 'datetime',
    ];

    public function message()
    {
        return $this->belongsTo(ChatUserMessage::class, 'message_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
//https://claude.ai/public/artifacts/1e0db81a-191f-4877-a6b5-397c22538a98
