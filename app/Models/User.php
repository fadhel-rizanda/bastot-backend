<?php

namespace App\Models;

use App\Models\career\Achievement;
use App\Models\career\Applicant;
use App\Models\career\CareerOpportunity;
use App\Models\career\School;
use App\Models\career\UserEducation;
use App\Models\community\UserCommunity;
use App\Models\court\Reservation;
use App\Models\game\Highlight;
use App\Models\game\PlayByPlay;
use App\Models\game\Stats;
use App\Models\game\UserTeam;
use App\Models\train\UserWorkoutPlan;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'profile_picture',
        'phone'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // UUID
    // protected static function boot()
    // {
    //     parent::boot();
    //     static::creating(function ($model) {
    //         $model->id = Str::uuid()->toString();
    //     });
    // }

    /**
     * Get the roles associated with the user.
     */
    public function role()
    {
        return $this->hasOne(Role::class, 'id', 'role_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function userCommunity()
    {
        return $this->hasMany(UserCommunity::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function careerOpportunities()
    {
        return $this->hasMany(CareerOpportunity::class);
    }

    public function applications(){
        return $this->hasMany(Applicant::class);
    }

    public function userTeam()
    {
        return $this->hasMany(UserTeam::class);
    }

    public function stats(){
        return $this->hasMany(Stats::class);
    }

    public function userWorkoutPlan(){
        return $this->hasMany(UserWorkoutPlan::class);
    }

    public function achievements(){
        return $this->hasMany(Achievement::class);
    }

    public function educations(){
        return $this->hasMany(UserEducation::class);
    }

    public function schools(){
        return $this->hasManyThrough(UserEducation::class, School::class);
    }

    public function playByPlays(){
        return $this->hasMany(PlayByPlay::class);
    }
}
