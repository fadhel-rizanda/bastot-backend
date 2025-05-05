<?php

namespace App\Models\game;

use Illuminate\Database\Eloquent\Model;

class TournamentGame extends Model
{
    protected $table = 'tournament_game';
    protected $fillable = [
        'tournament_id',
        'game_id',
        'round'
    ];
}
