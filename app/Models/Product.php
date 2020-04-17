<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'user_id',
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

    // 对产品点赞的用户
    public function likes()
    {
        return $this->belongsToMany(
            User::class,
            'user_product_likes',
            'product_id',
            'user_id'
        )->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
