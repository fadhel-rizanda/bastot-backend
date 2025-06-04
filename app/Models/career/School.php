<?php

namespace App\Models\career;

use App\Models\Location;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $table = 'schools';
    protected $fillable = [
      'name',
      'description',
      'additional_link',
      'address',
      'latitude',
      'longitude',
      'location_id',
    ];

    public function students()
    {
        return $this->hasMany(UserEducation::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
