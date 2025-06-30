<?php

namespace App\Models\community;

use App\Models\Review;
use App\Models\Status;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'events';
    protected $fillable = [
        'name',
        'description',
        'price',
        'address',
        'latitude',
        'longitude',
        'location_id',
        'community_id',
        'status_id',
        'start_time',
        'end_time',
    ];

    public function community(){
        return $this->belongsTo(Community::class);
    }

    public function tags(){
        return $this->belongsToMany(Tag::class, 'event_tag', 'event_id', 'tag_id');
    }

    public function status(){
        return $this->belongsTo(Status::class);
    }

    public function reviews(){
        return $this->belongsToMany(Review::class, 'event_review', 'event_id', 'review_id');
    }
}
