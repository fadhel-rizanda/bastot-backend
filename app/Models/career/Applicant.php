<?php

namespace App\Models\career;

use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    protected $table = 'applicants';
    protected $fillable = [
        'career_opportunity_id',
        'user_id',
        'requirements_link',
        'status_id',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
    public function careerOpportunity(){
        return $this->belongsTo(CareerOpportunity::class, 'career_opportunity_id');
    }
}
