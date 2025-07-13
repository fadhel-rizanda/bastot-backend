<?php

namespace App\Models;

use App\Enums\Type;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
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
        'id',
        'user_id',
        'type',
        'title',
        'message',
        'is_read',
    ];

    protected $casts = [
        'type' => Type::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
