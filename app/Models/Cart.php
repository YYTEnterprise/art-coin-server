<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    const CART_STATUS_PENDING = 'pending';
    const CART_STATUS_EXPIRED = 'expired';
    const CART_STATUS_COMPLETE = 'complete';

    protected $fillable = [
        'status',
    ];

    public function items()
    {
        return $this->hasMany(CartItem::class)
            ->with('product');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}