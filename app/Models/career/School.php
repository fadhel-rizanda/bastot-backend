<?php

namespace App\Models\career;

use App\Models\Location;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) \Str::uuid();
            }
        });
    }
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
