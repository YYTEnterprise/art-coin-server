<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    const PAY_METHOD_ART_COIN = 'art_coin';

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
        return $this->hasMany(OrderItem::class)
            ->with('product');
    }

    public function shipping() {
        return $this->hasOne(Shipping::class);
    }

    public function pays()
    {
        return $this->hasMany(OrderPay::class);
    }

    public function refunds()
    {
        return $this->hasMany(OrderRefund::class);
    }

    public static function new($user, $product, $amount, $shippingArray = [])
    {
        DB::beginTransaction();
        $orderArray = [
            'sale_way' => $product['sale_way'],
            'total_amount' => $amount,
            'seller_id' => $product['user_id'],
            'pay_method' => Order::PAY_METHOD_ART_COIN,
        ];
        $order = $user->buyOrders()->create($orderArray);

        $shippingArray['seller_id'] = $product['user_id'];
        $shippingArray['status'] = Shipping::STATUS_PENDING;
        $order->shipping()->create($shippingArray);

        $orderItemArray = [
            'product_id' => $product['id'],
            'price' => $product['price'],
            'amount' => $amount,
            'count' => 1,
        ];
        $order->items()->create($orderItemArray);
        DB::commit();

        return $order;
    }
}
