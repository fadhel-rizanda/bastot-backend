<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Laravel\Passport\Client as PassportClient;
use Illuminate\Support\Str;

class Client extends PassportClient
{
    public $incrementing = false;
    protected $keyType = 'string';

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }
}
