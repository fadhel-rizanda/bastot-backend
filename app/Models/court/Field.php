<?php

namespace App\Models\court;

use App\Models\game\Game;
use App\Models\Review;
use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    protected $table = 'fields';
    protected $fillable = [
        'id',
        'court_id',
        'name',
        'image',
        'default_price_per_hour',
        'is_available'
    ];

    public function court(){
        return $this->belongsTo(Court::class);
    }

    public function games(){
        return $this->hasMany(Game::class);
    }

    public function schedules(){
        return $this->hasMany(Schedule::class);
    }

    public function reviews(){
        return $this->hasMany(Review::class);
    }
}
