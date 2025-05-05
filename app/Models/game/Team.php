<?php

namespace App\Models\game;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $table = 'teams';
    protected $fillable =[
        'name',
        'logo'
    ];
}
