<?php

namespace App\Models\game;

use App\Models\Review;
use Illuminate\Database\Eloquent\Model;

class Highlight extends Model
{
    protected $table = 'highlights';
    protected $fillable  =[
        'stat_id',
        'content',
        'notes'
    ];

    public function stat(){
        return $this->belongsTo(Stats::class);
    }

    public function review(){
        return $this->belongsToMany(Review::class, 'highlight_review', 'highlight_id', 'review_id');
    }
}
