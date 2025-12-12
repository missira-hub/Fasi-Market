<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar', // ✅ Only real columns
        'phone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['avatar_url'];

    // ✅ Computed avatar URL
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return '/storage/' . $this->avatar;
        }
        return '/default-avatar.png';
    }

    // --- Roles ---
    public function isFarmer()
    {
        return $this->role === 'farmer';
    }

    public function isConsumer()
    {
        return $this->role === 'consumer';
    }

    // --- Feedback ---
    public function feedback()
    {
        return $this->hasMany(Feedback::class, 'farmer_id')->where('approved', true);
    }

    public function averageRating()
    {
        return $this->feedback()->avg('rating');
    }

    public function feedbackGiven()
    {
        return $this->hasMany(Feedback::class, 'user_id');
    }

    // --- Products ---
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function receivedFeedback()
    {
        return $this->hasManyThrough(Feedback::class, Product::class);
    }

    // --- Conversations ---
   public function conversations()
{
    return $this->belongsToMany(
        Conversation::class,
        'conversation_participants', // match the pivot table
        'user_id',
        'conversation_id'
    );
}

    // --- Messages ---
    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    // --- Cart ---
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    // --- Profile Types ---
    public function consumer()
    {
        return $this->hasOne(Consumer::class);
    }

    public function farmer()
    {
        return $this->hasOne(Farmer::class);
    }

    public function admin()
    {
        return $this->hasOne(Admin::class);
    }

public function cartItems()
{
    return $this->hasMany(Cart::class);
}
}