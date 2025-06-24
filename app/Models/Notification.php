<?php

namespace App\Models;

use App\Enums\Enums\Type;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $fillable = [
        'id',
        'user_id',
        'type',
        'title',
        'message',
        'is_read',
        'color'
    ];

    protected $casts = [
        'type' => Type::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
