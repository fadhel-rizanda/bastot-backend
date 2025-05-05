<?php

namespace App\Models\train;

use Illuminate\Database\Eloquent\Model;

class WorkoutPlan extends Model
{
    protected $table = 'workout_plan';
    protected $fillable = [
        'name',
        'description',
        'duration',
        'image',
        'difficulty',
    ];
}
