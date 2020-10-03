<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TradeInfo extends Model
{
    protected $fillable = [
        'trader_id',
        'max_amount',
        'max_usd_amount',
        'min_usd_amount',
        'price',
        'trade_type',
    ];

    public function trader()
    {
        return $this->belongsTo(Trader::class);
    }
}
