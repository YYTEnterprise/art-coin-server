<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    const TYPE_ART = 'art';
    const TYPE_IDEA = 'idea';
    const TYPE_MEMORY = 'memory';

    protected $fillable = [
        'title', 'description', 'image', 'on_sale', 'price', 'type', 'user_id',
    ];

    protected $casts = [
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
}
