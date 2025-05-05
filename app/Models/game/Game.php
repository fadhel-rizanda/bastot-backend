<?php

namespace App\Models\game;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $table = 'games';
    protected $fillable = [
      'name',
      'description',
      'court_id',
      'home_team_id',
      'away_team_id'
    ];
}
