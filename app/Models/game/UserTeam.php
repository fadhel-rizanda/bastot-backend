<?php

namespace App\Models\game;

use App\Models\Role;
use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserTeam extends Model
{
    protected $table = 'user_team';
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

    public function role(){
        return $this->belongsTo(Role::class);
    }

    public function status(){
        return $this->belongsTo(Status::class);
    }
}
