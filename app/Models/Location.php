<?php

namespace App\Models;

use App\Models\community\Event;
use App\Models\court\Court;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $table = 'locations';
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
