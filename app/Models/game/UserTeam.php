<?php

namespace App\Models\game;

use App\Models\Role;
use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserTeam extends Model
{
    protected $table = 'user_team';
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
        'user_id',
        'team_id',
        'role_id',
        'status_id',
        'notes'
    ];

    public function team(){
        return $this->belongsTo(Team::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    // Di Team.php
    public function users()
    {
        return $this->hasManyThrough(User::class, UserTeam::class, 'team_id', 'id', 'id', 'user_id');
    }

    public function userStats($gameId)
    {
        return $this->user->stats()->where('game_id', $gameId)->get();
    }

    public function role(){
        return $this->belongsTo(Role::class);
    }

    public function status(){
        return $this->belongsTo(Status::class);
    }
}
