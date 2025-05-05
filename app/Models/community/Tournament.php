<?php

namespace App\Models\community;

use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    protected $table = 'tournaments';
    protected $fillable = [
        'event_id',
        'court_id'
    ];
}
