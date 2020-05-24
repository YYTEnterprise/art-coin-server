<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
        'status',
        'start_at',
        'end_at',
    ];

    protected $dates = [
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

    // 用户竞标
    public function newBid($userId, $bidAmount) {
        DB::beginTransaction();
        // 更新当前竞标价
        $this->update([
            'bid_user_id' => $userId,
            'current_price' => $bidAmount,
        ]);
        // 新增一条竞标数据
        $this->bids()->create([
            'user_id' => $userId,
            'bid_price' => $bidAmount,
            'bid_at' => now(),
        ]);
        // 锁定金额
        user::findOrFail($userId)->wallet()->lock($bidAmount);
        DB::commit();
    }

    // 用户一口价
    public function newFixed($userId, $fixedAmount) {
        DB::beginTransaction();
        // 更新当选竞标
        $this->update([
            'bid_user_id' => $userId,
            'purchase_price' => $fixedAmount,
            'status' => Auction::STATUS_FIXED_SUCCESS,
        ]);
        // 锁定金额
        user::findOrFail($userId)->wallet()->lock($fixedAmount);
        // 创建订单
        DB::commit();
    }
}
