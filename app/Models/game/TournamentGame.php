<?php

namespace App\Models\game;

use App\Models\community\Tournament;
use Illuminate\Database\Eloquent\Model;

class TournamentGame extends Model
{
    protected $table = 'tournament_game';
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
