<?php

namespace App\Models\game;

use Illuminate\Database\Eloquent\Model;

class UserTeam extends Model
{
    protected $table = 'user_team';
    protected $fillable = [
        'user_id',
        'team_id',
        'role_id'
    ];
}
