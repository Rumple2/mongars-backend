<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Couple extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'user1_id',
        'user2_id',
        'started_at',
        'ended_at',
        'is_active',
    ];

    protected $casts = [
        'id' => 'string',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
            if (empty($model->started_at)) {
                $model->started_at = now();
            }
        });
    }

    public function user1()
    {
        return $this->belongsTo(User::class, 'user1_id');
    }

    public function user2()
    {
        return $this->belongsTo(User::class, 'user2_id');
    }
}
