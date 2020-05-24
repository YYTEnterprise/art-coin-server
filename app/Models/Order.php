<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    const PAY_STATUS_PENDING = 'pending';
    const PAY_STATUS_PAYING = 'paying';
    const PAY_STATUS_PAID = 'paid';
    const PAY_STATUS_PAID_FAILED = 'pay_failed';
    const PAY_STATUS_REFUNDING = 'refunding';
    const PAY_STATUS_REFUNDED = 'refunded';
    const PAY_STATUS_REFUNDED_FAILED = 'refunded_failed';
    const PAY_STATUS_COMPLETE = 'complete';

    protected $fillable = [
        'user_id',
        'sale_way',
        'total_amount',
        'pay_method',
        'status',
        'order_pay_id',
        'order_refund_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shipping() {
        return $this->hasOne(Shipping::class);
    }

    public function orderPays()
    {
        return $this->hasMany(OrderPay::class);
    }

    public function orderRefunds()
    {
        return $this->hasMany(OrderRefund::class);
    }
}
