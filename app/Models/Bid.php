<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bid extends Model
{

    protected $fillable = [
        'auction_id',
        'user_id',
        'bid_price',
        'bid_at',
    ];

    protected $dates = [
        'bid_at',
    ];

    public function auction()
    {
        return $this->belongsTo(Auction::class);
    }

    public function bidUser() {
        return $this->belongsTo(User::class);
    }
}
