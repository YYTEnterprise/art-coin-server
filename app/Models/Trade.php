<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trade extends Model
{
    protected $fillable = [
        'trader_id',
        'buyer_id',
        'amount',
        'usd_amount',
        'price',
        'trade_type',
        'trade_account',
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
