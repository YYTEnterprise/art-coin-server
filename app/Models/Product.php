<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    const SALE_WAY_DIRECT = 'direct';
    const SALE_WAY_AUCTION = 'auction';

    protected $fillable = [
        'user_id',
        'stock_quantity',
        'title',
        'brief_desc',
        'detail_desc',
        'cover_image',
        'price',
        'deliver_type',
        'has_deliver_fee',
        'has_tariff',
        'deliver_remark',
        'on_sale',
        'sale_way',
    ];

    protected $casts = [
        'has_deliver_fee' => 'boolean',
        'has_tariff' => 'boolean',
        'on_sale' => 'boolean',
    ];

    protected $hidden = [
        'pivot',
    ];

    // 对产品点赞的用户
    public function likes()
    {
        return $this->belongsToMany(
            User::class,
            'user_product_likes',
            'product_id',
            'user_id'
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 拍卖，sale_way = auction 时存在该数据
    public function auction()
    {
        return $this->hasOne(Auction::class)
            ->with('bids');
    }

    public function orderItem() {
        return $this->hasOne(OrderItem::class);
    }
}
