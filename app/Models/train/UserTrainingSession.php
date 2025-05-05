<?php

namespace App\Models\train;

use Illuminate\Database\Eloquent\Model;

class UserTrainingSession extends Model
{
    protected $table = 'user_training_session';
    protected $fillable = [
        'user_id',
        'workout_plan_id',
        'progress',
    ];
}
