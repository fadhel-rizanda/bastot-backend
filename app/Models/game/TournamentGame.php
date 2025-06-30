<?php

namespace App\Models\game;

use App\Models\community\Tournament;
use Illuminate\Database\Eloquent\Model;

class TournamentGame extends Model
{
    protected $table = 'tournament_game';
    protected $fillable = [
        'tournament_id',
        'game_id',
        'round'
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
