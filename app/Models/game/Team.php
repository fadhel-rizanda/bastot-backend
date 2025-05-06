<?php

namespace App\Models\game;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $table = 'teams';
    protected $fillable =[
        'name',
        'logo'
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function userTeam()
    {
        return $this->hasMany(UserTeam::class);
    }

    public function users(){
        return $this->belongsToMany(User::class, 'user_team', 'team_id', 'user_id');
    }
}
