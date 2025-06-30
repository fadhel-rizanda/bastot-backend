<?php

namespace App\Models\game;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $table = 'teams';
    protected $fillable =[
        'name',
        'initial',
        'logo',
        'team_owner_id'
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function userTeam()
    {
        return $this->hasMany(UserTeam::class);
    }

    public function isFull()
    {
        return $this->userTeam()->where('status_id', 'ACTIVE')->count() >= (env("MAX_TEAM_SIZE") ?? 10);
    }

    public function users(){
        return $this->belongsToMany(User::class, 'user_team', 'team_id', 'user_id');
    }

    public function teamOwner(){
        return $this->belongsTo(User::class, 'team_owner_id');
    }

    public function playByPlays(){
        return $this->hasMany(PlayByPlay::class);
    }
}
