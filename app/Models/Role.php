<?php

namespace App\Models;

use App\Models\community\UserCommunity;
use App\Models\game\UserTeam;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';
    protected $fillable = [
        'id',
        'name',
        'type',
        'description',
        'color'
    ];
//    biar id ga 0 pas return
    protected $keyType = 'string';
    public $incrementing = false;


    public function user()
    {
        return $this->belongsTo(User::class, 'role_id', 'id');
    }

    public function userCommunity()
    {
        return $this->hasMany(UserCommunity::class, 'role_id', 'id');
    }

    public function userTeam(){
        return $this->hasMany(UserTeam::class, 'role_id', 'id');
    }
}
