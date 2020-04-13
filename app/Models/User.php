<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'email_verified_at', 'api_token', 'pivot'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    // 关注我的人，相当于微博中的“粉丝”
    public function followers()
    {
        return $this->belongsToMany(
            User::class,
            'user_follows',
            'follow_user_id',
            'user_id'
        )->withTimestamps();
    }

    // 我关注的人，相当于微博中的“关注”
    public function followings()
    {
        return $this->belongsToMany(
            User::class,
            'user_follows',
            'user_id',
            'follow_user_id'
        )->withTimestamps();
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function auctions()
    {
        return $this->hasMany(Auction::class);
    }
}
