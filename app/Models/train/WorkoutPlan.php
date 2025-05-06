<?php

namespace App\Models\train;

use App\Models\Tag;
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

    public function trainingSessions()
    {
        return $this->hasMany(TrainingSession::class);
    }

    public function userWorkoutPlan()
    {
        return $this->hasMany(UserWorkoutPlan::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'workout_plan_tag', 'workout_plan_id', 'tag_id');
    }
}
