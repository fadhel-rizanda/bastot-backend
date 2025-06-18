<?php

namespace App\Models\court;

use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $table = 'reservations';
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
