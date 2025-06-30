<?php

namespace App\Models\court;

use App\Models\Location;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Court extends Model
{
    protected $table = 'courts';
    protected $fillable = [
        'name',
        'description',
        'profile_picture',
        'address',
        'latitude',
        'longitude',
        'location_id',
    ];

    public function location(){
        return $this->belongsTo(Location::class);
    }

    public function owner(){
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function reviews(){
        return $this->belongsToMany(Review::class, 'court_review', 'court_id', 'review_id');
    }
}
