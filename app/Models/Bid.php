<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bid extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'auction_id',
        'user_id',
        'bid_price',
        'locked',
        'bid_at',
    ];

    protected $dates = [
        'bid_at',
    ];

    public function auction()
    {
        return $this->belongsTo(Auction::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
