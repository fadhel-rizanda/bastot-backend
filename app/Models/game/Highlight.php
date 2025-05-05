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
}
