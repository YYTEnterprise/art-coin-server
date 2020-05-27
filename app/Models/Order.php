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
    const PAY_STATUS_REFUNDED = 'refund';
    const PAY_STATUS_REFUNDED_FAILED = 'refund_failed';
    const PAY_STATUS_COMPLETE = 'complete';

    protected $fillable = [
        'buyer_id',
        'seller_id',
        'sale_way',
        'total_amount',
        'pay_method',
        'status',
        'order_pay_id',
        'order_refund_id',
    ];

    public function buyer()
    {
        return $this->belongsTo(User::class, 'id', 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'id', 'seller_id');
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
