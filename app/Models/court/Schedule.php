<?php

namespace App\Models\court;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $table = 'schedules';
    protected $fillable = [
        'court_id',
        'start_time',
        'end_time',
        'price',
        'is_available'
    ];
}
