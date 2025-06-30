<?php

namespace App\Models\community;

use App\Models\Role;
use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserCommunity extends Model
{
    protected $table = 'user_community';
    protected $fillable = [
        'user_id',
        'community_id',
        'role_id',
        'status_id'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function community(){
        return $this->belongsTo(Community::class);
    }

    public function role(){
        return $this->belongsTo(Role::class);
    }

    public function status(){
        return $this->belongsTo(Status::class);
    }
}
