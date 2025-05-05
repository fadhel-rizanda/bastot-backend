<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $table = 'locations';
    protected $fillable = [
        'name',
        'place_id',
        'residential',
        'village',
        'city',
        'state',
        'region',
        'country',
        'country_code',
        'postal_code',
    ];
}
