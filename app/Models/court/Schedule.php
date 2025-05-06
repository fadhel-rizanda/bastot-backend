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

    public function court()
    {
        return $this->belongsTo(Court::class);
    }

    public function reservations(){
        return $this->hasMany(Reservation::class);
    }
}
