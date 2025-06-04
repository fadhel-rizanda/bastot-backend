<?php

namespace App\Models;

use App\Models\community\Event;
use App\Models\court\Court;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $table = 'locations';
    protected $fillable = [
        'id',
        'name',
        'residential',
        'village',
        'city',
        'state',
        'region',
        'country',
        'country_code',
        'postcode',
    ];

    public function courts(){
        return $this->hasMany(Court::class);
    }

    public function events(){
        return $this->hasMany(Event::class);
    }
}
