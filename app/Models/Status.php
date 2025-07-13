<?php

namespace App\Models;

use App\Enums\Type;
use App\Models\career\Applicant;
use App\Models\court\Reservation;
use App\Models\game\PlayByPlay;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $table = 'statuses';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'name',
        'type',
        'description',
        'color',
    ];
    protected $casts = [
        'type' => Type::class,
    ];

    public function reservations(){
        return $this->hasMany(Reservation::class, 'status_id', 'id');
    }
    public function applicants(){
        return $this->hasMany(Applicant::class, 'status_id', 'id');
    }

    public function playByPlays(){
        return $this->hasMany(PlayByPlay::class, 'status_id', 'id');
    }
}
