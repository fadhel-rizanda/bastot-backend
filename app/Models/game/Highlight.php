<?php

namespace App\Models\game;

use Illuminate\Database\Eloquent\Model;

class Highlight extends Model
{
    protected $table = 'highlights';
    protected $fillable  =[
        'stat_id',
        'content',
        'type'
    ];

    public function stat(){
        return $this->belongsTo(Stats::class);
    }
}
