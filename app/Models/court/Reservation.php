<?php

namespace App\Models\court;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $table = 'reservations';
    protected $fillable = [
        'schedule_id',
        'user_id',
        'status_id'
    ];
}
