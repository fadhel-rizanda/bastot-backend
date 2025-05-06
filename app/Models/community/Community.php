<?php

namespace App\Models\community;

use App\Models\Review;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Community extends Model
{
    protected $table = 'communities';
    protected $fillable = [
        'name',
        'description',
        'base_court',
    ];

    public function userCommunity()
    {
        return $this->hasMany(UserCommunity::class);
    }

    public function tournaments()
    {
        return $this->hasMany(Tournament::class);
    }

    public function events(){
        return $this->hasMany(Event::class);
    }

    public function tags(){
        return $this->belongsToMany(Tag::class, 'community_tag', 'community_id', 'tag_id');
    }

    public function reviews(){
        return $this->hasMany(Review::class);
    }

    public function users(){
        return $this->belongsToMany( User::class, 'user_community', 'community_id', 'user_id');
    }
}
