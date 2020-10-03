<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trader extends Model
{
    protected $fillable = [
        'name',
        'trade_type',
        'trade_account',
    ];

    public function tradeInfos()
    {
        return $this->hasMany(TradeInfo::class);
    }

    public function trades()
    {
        return $this->hasMany(Trade::class);
    }
}
