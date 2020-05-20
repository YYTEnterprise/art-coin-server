<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipping extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_RECEIVED = 'received';

    protected $fillable = [
        'order_id',
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
        'status',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function getFullAddressAttribute()
    {
        return "{$this->country}{$this->province}{$this->city}{$this->street}";
    }
}
