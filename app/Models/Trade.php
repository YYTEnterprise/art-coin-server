<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trade extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_COMPLETE = 'confirmed';
    const STATUS_CANCEL = 'canceled';

    protected $fillable = [
        'trade_info_id',
        'buyer_id',
        'amount',
        'usd_amount',
        'price',
        'trade_type',
        'trade_account',
        'status',
    ];

    public function tradeInfo()
    {
        return $this->belongsTo(TradeInfo::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }
}
