<?php

namespace App\Models\court;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $table = 'schedules';
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
        'field_id',
        'start_time',
        'end_time',
        'price_per_hour',
        'is_available'
    ];

    public function field()
    {
        return $this->belongsTo(Field::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
