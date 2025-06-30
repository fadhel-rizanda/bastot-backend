<?php

namespace App\Models\game;

use App\Enums\Enums\Type;
use App\Models\Status;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PlayByPlay extends Model
{
    protected $table = 'play_by_play';

    protected $fillable = [
        'game_id',
        'user_id',
        'team_id',
        'status_id',

        'quarter',
        'time_seconds',
        'home_score',
        'away_score',
        'title',
        'description',
    ];

    protected $casts = [
        'game_id' => 'integer',
        'user_id' => 'integer',
        'team_id' => 'integer',
        'status' => 'integer'
    ];

    public function game(){
        return $this->belongsTo(Game::class, 'game_id');
    }

    public function team(){
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tags(){
        return $this->belongsToMany(Tag::class, 'play_by_play_tag', 'play_by_play_id', 'tag_id');
    }

    public function status(){
        return $this->belongsTo(Status::class, 'status_id');
    }
}
