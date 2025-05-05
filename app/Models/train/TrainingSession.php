<?php

namespace App\Models\train;

use Illuminate\Database\Eloquent\Model;

class TrainingSession extends Model
{
    protected $table = 'training_session';
    protected $fillable = [
        'name',
        'description',
        'video',
        'duration',
        'workout_plan_id'
    ];
}
