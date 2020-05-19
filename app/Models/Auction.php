<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auction extends Model
{
    const STATUS_INITIAL = 'initial';
    const STATUS_BIDDING = 'bidding';
    const STATUS_BID_FAIL = 'bid_fail';
    const STATUS_BID_SUCCESS = 'bid_success';
    const STATUS_FIXED_SUCCESS = 'fixed_success';

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
    public function currentBidUser()
    {
        return $this->belongsTo(User::class, 'bid_user_id');
    }

    public function bids() {
        return $this->hasMany(Bid::class);
    }
}
