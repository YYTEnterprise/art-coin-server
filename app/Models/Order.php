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
    const PAY_STATUS_CANCEL = 'cancel';

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
        return $this->belongsTo(User::class, 'buyer_id', 'id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id', 'id');
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

    public static function newFromCart($user, $cart, $shippingArray = [])
    {
        $cartItems = $cart->items;
        DB::beginTransaction();
        $orderArray = [
            'sale_way' => Product::SALE_WAY_DIRECT,
            'total_amount' => $cart->totalAmount(),
            'seller_id' => 0,
            'pay_method' => Order::PAY_METHOD_ART_COIN,
        ];
        $order = $user->buyOrders()->create($orderArray);

        // TODO
        $shippingArray['seller_id'] = 0;
        $shippingArray['status'] = Shipping::STATUS_PENDING;
        $order->shipping()->create($shippingArray);

        foreach ($cartItems as $item) {
            $orderItemArray = [
                'product_id' => $item['id'],
                'price' => $item['price'],
                'amount' => $item['amount'],
                'count' => $item['count'],
            ];
            $order->items()->create($orderItemArray);
        }
        $cart->update([
            'status' => Cart::CART_STATUS_COMPLETE
        ]);

        DB::commit();

        return $order;
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
