<?php

namespace App\Models;

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
    ];

    public function tournament(){
        return $this->belongsTo(Tournament::class);
    }

    public function court(){
        return $this->belongsTo(Court::class);
    }

    public function field(){
        return $this->belongsTo(Field::class);
    }
}
