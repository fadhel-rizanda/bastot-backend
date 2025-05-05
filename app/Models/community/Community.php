<?php

namespace App\Models\community;

use Illuminate\Database\Eloquent\Model;

class Community extends Model
{
    protected $table = 'communities';
    protected $fillable = [
        'name',
        'description',
        'base_court',
    ];
}
