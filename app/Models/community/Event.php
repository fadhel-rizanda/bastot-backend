<?php

namespace App\Models\community;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'events';
    protected $fillable = [
        'name',
        'description',
        'address',
        'latitude',
        'longitude',
        'location_id',
        'community_id',
        'start_time',
        'end_time',
    ];

    public function community(){
        return $this->belongsTo(Community::class);
    }

    public function tags(){
        return $this->belongsToMany(Tag::class, 'event_tag', 'event_id', 'tag_id');
    }
}
