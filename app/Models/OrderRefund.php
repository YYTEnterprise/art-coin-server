<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderRefund extends Model
{
    protected $fillable = [
        'order_id',
        'origin_tx_no',
        'refund_at',
        'tx_msg',
    ];

    protected $dates = [
        'refund_at',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
