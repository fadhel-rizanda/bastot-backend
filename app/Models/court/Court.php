<?php

namespace App\Models\court;

use App\Models\Location;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Court extends Model
{
    protected $table = 'courts';
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
        'name',
        'description',
        'profile_picture',
        'address',
        'latitude',
        'longitude',
        'location_id',
    ];

    public function location(){
        return $this->belongsTo(Location::class);
    }

    public function owner(){
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function reviews(){
        return $this->belongsToMany(Review::class, 'court_review', 'court_id', 'review_id');
    }

    public function fields(){
        return $this->hasMany(Field::class);
    }
}
