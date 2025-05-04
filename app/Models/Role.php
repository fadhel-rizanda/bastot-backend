<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';
    protected $fillable = [
        'id',
        'name',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'role_id', 'id');
    }
}
