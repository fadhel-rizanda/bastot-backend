<?php

namespace App\Models\career;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CareerOpportunity extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) \Str::uuid();
            }
        });
    }
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

    public function provider()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function applicants(){
        return $this->hasMany(Applicant::class, 'career_opportunity_id');
    }
}
