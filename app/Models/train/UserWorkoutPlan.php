<?php

namespace App\Models\train;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserWorkoutPlan extends Model
{
    protected $table = 'user_workout_plan';
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
        'workout_plan_id',
        'progress',
    ];

    public function workoutPlan(){
        return $this->belongsTo(WorkoutPlan::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
