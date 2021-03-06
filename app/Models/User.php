<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_url',
        'cover_image_url',
        'country',
        'pay_passwd',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at',
        'api_token',
        'pay_passwd',
        'pivot',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class)
            ->with('product');
    }

    public function cartTotalAmount() {
        $totalItems = $this->cartItems;
        $totalAmount = 0;
        foreach ($totalItems as $item) {
            $totalAmount += $item['amount'] * $item['count'];
        }

        return $totalAmount;
    }

    // 关注我的人，相当于微博中的“粉丝”
    public function followers()
    {
        return $this->belongsToMany(
            User::class,
            'user_follows',
            'follow_user_id',
            'user_id'
        )->withTimestamps();
    }

    // 我关注的人，相当于微博中的“关注”
    public function followings()
    {
        return $this->belongsToMany(
            User::class,
            'user_follows',
            'user_id',
            'follow_user_id'
        )->withTimestamps();
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // 用户在自己产品下创建的所有拍卖
    public function auctions()
    {
        return $this->hasManyThrough(Auction::class, Product::class);
    }

    public function sellOrders()
    {
        return $this->hasMany(Order::class, 'seller_id');
    }

    public function buyOrders()
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    // 用户参与的竞标，且用户出价最高
    public function bidAuctions()
    {
        return $this->hasMany(Auction::class, 'bid_user_id');
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function trades()
    {
        return $this->hasMany(Trade::class, 'buyer_id');
    }

    // 用户点赞过的物品
    public function likes()
    {
        return $this->belongsToMany(
            Product::class,
            'user_product_likes',
            'user_id',
            'product_id',
        );
    }

    // 用户是否对物品点赞
    public function isLikedProduct($product_id) {
        $userId = $this->id;
        $isLike =
            UserProductLike::where('user_id', $userId)
                ->where('product_id', $product_id)
                ->get()->isNotEmpty();
        return [
            'is_like' => $isLike,
        ];
    }

    public function isFollowing($following_user_id) {
        $isFollowing =
            $this->followings()
                ->where('id', $following_user_id)
                ->get()->isNotEmpty();
        return [
            'is_following' => $isFollowing,
        ];
    }

    public function transfer($toId, $amount) {
        DB::beginTransaction();
        $this->wallet->withdraw($amount);
        User::findOrFail($toId)->wallet->deposit($amount);
        DB::commit();
    }

    public function unlockAndTransfer($toId, $amount) {
        DB::beginTransaction();
        $this->wallet->unlock($amount);
        $this->wallet->withdraw($amount);
        User::findOrFail($toId)->wallet->deposit($amount);
        DB::commit();
    }
}
