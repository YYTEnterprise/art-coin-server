<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trade extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_COMPLETE = 'complete';
    const STATUS_CANCEL = 'cancel';

    protected $fillable = [
        'trader_id',
        'buyer_id',
        'amount',
        'usd_amount',
        'price',
        'trade_type',
        'trade_account',
        'status',
    ];

    public function trader()
    {
        return $this->belongsTo(Trader::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class);
    }
}
