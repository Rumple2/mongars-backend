<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchHistory extends Model
{
    protected $fillable = [
        'id',
        'user_id',
        'search_query',
        'result_user_id',
        'result_status',
        'searched_at',
    ];

    protected $casts = [
        'id' => 'string',
        'searched_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function resultUser()
    {
        return $this->belongsTo(User::class, 'result_user_id');
    }
}
