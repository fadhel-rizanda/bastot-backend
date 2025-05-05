<?php

namespace App\Models\court;

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
}
