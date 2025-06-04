<?php

namespace App\Models\community;

use Illuminate\Database\Eloquent\Model;

class UserCommunity extends Model
{
    protected $table = 'user_community';
    protected $fillable = [
        'user_id',
        'community_id',
        'role_id',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function community(){
        return $this->belongsTo(Community::class);
    }
}
