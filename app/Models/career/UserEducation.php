<?php

namespace App\Models\career;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserEducation extends Model
{
    protected $table = 'user_education';
    protected $fillable = [
        'user_id',
        'school_id',
        'degree',
        'grade',
        'activities',
        'start_date',
        'end_date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
