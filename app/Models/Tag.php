<?php

namespace App\Models;

use App\Enums\Enums\Type;
use App\Models\community\Community;
use App\Models\community\Event;
use App\Models\community\Tournament;
use App\Models\game\Game;
use App\Models\train\TrainingSession;
use App\Models\train\WorkoutPlan;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $table = 'tags';
    protected $fillable = [
        'id',
        'name',
        'description',
        'color',
        'type'
    ];
    protected $keyType = 'string';
    public $incrementing = false;
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
}
