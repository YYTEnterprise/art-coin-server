<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPay extends Model
{
    protected $fillable = [
        'order_id',
        'origin_tx_no',
        'pay_at',
        'tx_msg',
    ];

    protected $dates = [
        'pay_at',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
