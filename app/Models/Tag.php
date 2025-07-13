<?php

namespace App\Models;

use App\Enums\Type;
use App\Models\community\Community;
use App\Models\community\Event;
use App\Models\community\Tournament;
use App\Models\game\PlayByPlay;
use App\Models\train\TrainingSession;
use App\Models\train\WorkoutPlan;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $table = 'tags';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'id',
        'name',
        'description',
        'color',
        'type'
    ];
    protected $casts = [
        'type' => Type::class,
    ];

    public function communities()
    {
        return $this->belongsToMany(Community::class, 'community_tag', 'tag_id', 'community_id');
    }

    public function tournaments()
    {
        return $this->belongsToMany(Tournament::class, 'tournament_tag', 'tag_id', 'tournament_id');
    }

    public function events(){
        return $this->belongsToMany(Event::class, 'event_tag', 'tag_id', 'event_id');
    }

    public function workoutPlans(){
        return $this->belongsToMany(WorkoutPlan::class, 'workout_plan_tag', 'tag_id', 'workout_plan_id');
    }

    public function trainingSessions()
    {
        return $this->belongsToMany(TrainingSession::class, 'training_session_tag', 'tag_id', 'training_session_id');
    }

    public function playByPlays()
    {
        return $this->belongsToMany(PlayByPlay::class, 'play_by_play_tag', 'tag_id', 'play_by_play_id');
    }
}
