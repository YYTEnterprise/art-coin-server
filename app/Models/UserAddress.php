<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'company',
        'country',
        'province',
        'city',
        'street',
        'postcode',
    ];
    protected $appends = ['full_address'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFullAddressAttribute()
    {
        return "{$this->country} {$this->province} {$this->city} {$this->street}";
    }
}
