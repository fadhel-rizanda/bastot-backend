<?php

namespace App\Models\community;

use App\Models\court\Court;
use App\Models\game\Game;
use App\Models\game\TournamentGame;
use App\Models\Review;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    protected $table = 'tournaments';
    public $incrementing = false;
    protected $keyType = 'string';
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) \Str::uuid();
            }
        });
    }
    protected $fillable = [
        'name',
        'description',
        'price',
        'poster',
        'event_id',
        'court_id'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function tournamentGames()
    {
        return $this->hasMany(TournamentGame::class);
    }

    public function court()
    {
        return $this->belongsTo(Court::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'tournament_tag', 'tournament_id', 'tag_id');
    }

//    not sure if this is correct
//    public function community()
//    {
//        return $this->belongsTo(Community::class);
//    }

    public function games(){
        return $this->belongsToMany(Game::class, 'tournament_game', 'tournament_id', 'game_id');
    }

    public function reviews(){
        return $this->belongsToMany(Review::class, 'tournament_review', 'tournament_id', 'review_id');
    }
}
