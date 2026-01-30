<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    public function profileViews()
    {
        return $this->hasMany(\App\Models\ProfileView::class, 'viewed_id');
    }
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'name',
        'username',
        'email',
        'phone',
        'password',
        'avatar_url',
        'date_of_birth',
        'status',
        'auth_method',
        'is_verified',
        'is_premium',
        'couple_id',
        'last_seen_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'id' => 'string',
        'date_of_birth' => 'date',
        'is_verified' => 'boolean',
        'is_premium' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    public function couple()
    {
        return $this->belongsTo(Couple::class);
    }

    public function coupleRequestsSent()
    {
        return $this->hasMany(CoupleRequest::class, 'sender_id');
    }

    public function coupleRequestsReceived()
    {
        return $this->hasMany(CoupleRequest::class, 'receiver_id');
    }

    /**
     * Get the device tokens for the user.
     */
    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class);
    }

        public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }
}
