<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class Auction extends Model
{
    const STATUS_BIDDING = 'bidding';
    const STATUS_BID_EXPIRED = 'bid_expired';
    const STATUS_BID_SUCCESS = 'bid_success';
    const STATUS_FIXED_SUCCESS = 'fixed_success';

    protected $fillable = [
        'product_id',
        'start_price',
        'bid_user_id',
        'current_price',
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
        return $this->hasMany(Bid::class)
            ->orderBy('bid_at', 'desc');
    }

    // 用户竞标
    public function newBid($userId, $bidAmount) {
        $bidAmount = floatval($bidAmount);
        DB::beginTransaction();
        // 更新当前竞标价
        $this->update([
            'bid_user_id' => $userId,
            'current_price' => $bidAmount,
        ]);
        // 释放已竞标的的锁定金额到各个竞标者的账户
        $bids = $this->bids()->where('locked', true)->get();
        foreach ($bids as $bid) {
            $bid->user->wallet->unlock($bid['bid_price']);
            $bid->update([
                'locked' => false,
            ]);
        }
        // 检测用户金额是否大于 $bidAmount
        if (User::findOrFail($userId)->wallet->free_amount < $bidAmount) {
            throw new BadRequestHttpException('Not enough free balance');
        }
        // 新增一条竞标数据
        $this->bids()->create([
            'user_id' => $userId,
            'bid_price' => $bidAmount,
            'bid_at' => now(),
            'locked' => true,
        ]);
        // 锁定金额
        user::findOrFail($userId)->wallet->lock($bidAmount);
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
        // 释放已竞标的的锁定金额到各个竞标者的账户
        $bids = $this->bids()->where('locked', true)->get();
        foreach ($bids as $bid) {
            $bid->user->wallet->unlock($bid['lock_amount']);
            $bid->update([
                'locked' => false,
            ]);
        }
        // 检测用户金额是否大于 $fixedAmount
        if (User::findOrFail($userId)->wallet->free_amount < $fixedAmount) {
            throw new BadRequestHttpException('Not enough free balance');
        }
        // 锁定金额
        user::findOrFail($userId)->wallet->lock($fixedAmount);

        DB::commit();
    }

    // 竞标成功
    public function bidSuccess() {
        $this->update([
            'status' => Auction::STATUS_BID_SUCCESS,
        ]);
    }

    // 竞标失败
    public function bidFail() {
        $this->update([
            'status' => Auction::STATUS_BID_EXPIRED,
        ]);
    }

    public static function getExpiredAuctions() {
        return Auction::where('status', Auction::STATUS_BIDDING)
            ->where('end_at', '<=', now())->get();
    }

    public static function handleExpiredAuctions() {
        DB::beginTransaction();
        $expiredAuctions = self::getExpiredAuctions();
        foreach ($expiredAuctions as $auction) {
            $bids = $auction->bids;
            if (count($bids) === 0) {
                // 竞标失败
                $auction->update([
                    'status' => Auction::STATUS_BID_EXPIRED
                ]);
            } else {
                // 竞标成功
                $auction->update([
                    'purchase_price' => $auction['current_price'],
                    'status' => Auction::STATUS_BID_SUCCESS,
                ]);
                $user = $auction->currentBidUser;

                // 商品下架
                $product = $auction->product;
                $product->offSale();
                // 创建订单
                $amount = $auction['current_price'];
                Order::new($user, $product, $amount);
            }
        }
        DB::commit();
    }
}
