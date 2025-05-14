<?php

namespace App\Models\career;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    protected $table = 'achievements';
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'image',
        'additional_link',
        'type',
        'issue_date',
        'expiration_date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
