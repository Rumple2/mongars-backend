<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileView extends Model
{
    protected $fillable = [
        'id',
        'viewer_id',
        'viewed_id',
        'viewed_at',
        'ip_address',
    ];

    protected $casts = [
        'id' => 'string',
        'viewed_at' => 'datetime',
    ];

    public function viewer()
    {
        return $this->belongsTo(User::class, 'viewer_id');
    }

    public function viewedUser()
    {
        return $this->belongsTo(User::class, 'viewed_id');
    }
}
