<?php

namespace App\Models\career;

use Illuminate\Database\Eloquent\Model;

class CareerOpportunity extends Model
{
    protected $table = 'career_opportunity';
    protected $fillable = [
        'name',
        'description',
        'requirements',
        'benefits',
        'deadline',
        'external_links',
        'type',
        'is_active',
        'user_id',
    ];
}
