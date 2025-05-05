<?php

namespace App\Models\career;

use Illuminate\Database\Eloquent\Model;

class CareerOpportunityApplication extends Model
{
    protected $table = 'career_opportunity_application';
    protected $fillable = [
        'career_opportunity_id',
        'user_id',
        'requirements_link',
        'status_id',
    ];
}
