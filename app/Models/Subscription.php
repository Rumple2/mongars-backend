<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'id',
        'user_id',
        'plan',
        'started_at',
        'expires_at',
        'is_active',
        'payment_method',
        'amount',
        'currency',
    ];

    protected $casts = [
        'id' => 'string',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
