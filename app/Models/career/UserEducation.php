<?php

namespace App\Models\career;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserEducation extends Model
{
    protected $table = 'user_education';
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
        'user_id',
        'school_id',
        'degree',
        'grade',
        'activities',
        'start_date',
        'end_date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
