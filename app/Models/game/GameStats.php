<?php

namespace App\Models\game;

use Illuminate\Database\Eloquent\Model;

class GameStats extends Model
{
    protected $table = 'game_stats';
    protected $fillable = [
        'user_id',
        'game_id',
        'points',
        'rebounds',
        'assists',
        'steals',
        'blocks',
        'turnovers',
    ];
}
