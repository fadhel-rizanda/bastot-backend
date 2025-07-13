<?php

namespace App\Models\court;

use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $table = 'reservations';
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
        'schedule_id',
        'game_id',
        'user_id',
        'status_id'
    ];

    public function schedule(){
        return $this->belongsTo(Schedule::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function status(){
        return $this->belongsTo(Status::class);
    }
}
