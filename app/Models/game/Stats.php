<?php

namespace App\Models\game;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Stats extends Model
{
    protected $table = 'stats';
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

    public function game(){
        return $this->belongsTo(Game::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function highlights(){
        return $this->hasMany(Highlight::class);
    }
}
