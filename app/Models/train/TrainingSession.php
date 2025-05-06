<?php

namespace App\Models\train;

use App\Models\Tag;
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

    public function workoutPlan()
    {
        return $this->belongsTo(WorkoutPlan::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'training_session_tag', 'training_session_id', 'tag_id');
    }
}
