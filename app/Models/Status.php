<?php

namespace App\Models;

use App\Models\career\Applicant;
use App\Models\court\Reservation;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $table = 'statuses';
    protected $fillable = [
        'name',
        'type',
        'description',
        'color',
    ];
    protected $keyType = 'string';
    public $incrementing = false;

    public function reservations(){
        return $this->hasMany(Reservation::class, 'status_id', 'id');
    }

    public function applicants(){
        return $this->hasMany(Applicant::class, 'status_id', 'id');
    }
}
