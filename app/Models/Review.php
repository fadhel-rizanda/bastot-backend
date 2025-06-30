<?php

namespace App\Models;

use App\Models\community\Event;
use App\Models\community\Tournament;
use App\Models\court\Court;
use App\Models\court\Field;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'reviews';
    protected $fillable = [
      'title',
      'body',
      'rating',
        'user_id'
    ];

    public function tournaments(){
        return $this->belongsToMany(Tournament::class, 'tournament_review', 'review_id', 'tournament_id');
    }

    public function courts(){
        return $this->belongsToMany(Court::class, 'court_review', 'review_id', 'court_id');
    }

    public function fields(){
        return $this->belongsToMany(Field::class, 'field_review', 'review_id', 'field_id');
    }

    public function events(){
        return $this->belongsToMany(Event::class, 'event_review', 'review_id', 'event_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
