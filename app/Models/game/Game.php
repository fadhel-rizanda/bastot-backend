<?php

namespace App\Models\game;

use App\Models\court\Court;
use App\Models\court\Field;
use App\Models\Status;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $table = 'games';
    protected $fillable = [
        'name',
        'description',
        'field_id',
        'home_team_id',
        'away_team_id',
        'start_time',
        'end_time',
        'home_score',
        'away_score',
        'status_id',
        'tag_id'
    ];

    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id', 'id');
    }

    public function tournamentGame()
    {
        return $this->hasMany(TournamentGame::class);
    }

    public function field()
    {
        return $this->belongsTo(Field::class);
    }

    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    public function homeTeam()
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam()
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function getUserTeam($userId) // karena sudah eager load, tidak perlu lagi load user team mengguankan ()
    {
        $userTeam = $this->homeTeam->userTeam->where('user_id', $userId)->first();
        return $userTeam ?: $this->awayTeam->userTeam->where('user_id', $userId)->first();
    }

    public function getUserStats($userId)
    {
        $userTeam = $this->getUserTeam($userId);
        if ($userTeam) {
            return $this->stats()->where('user_id', $userId)->first();
        }
        return null;
    }

    public function stats()
    {
        return $this->hasMany(Stats::class);
    }

    public function playByPlays(){
        return $this->hasMany(PlayByPlay::class);
    }
}
