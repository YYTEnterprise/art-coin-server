<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auction extends Model
{

    protected $fillable = [
        'product_id',
        'start_price',
        'step_price',
        'fixed_price',
        'start_at',
        'end_at',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // 当前竞标用户
    public function bidUser()
    {
        return $this->belongsTo(User::class, 'bid_user_id');
    }
}
