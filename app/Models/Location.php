<?php

namespace App\Models;

use App\Models\community\Event;
use App\Models\court\Court;
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

    public function courts(){
        return $this->hasMany(Court::class);
    }

    public function events(){
        return $this->hasMany(Event::class);
    }
}
