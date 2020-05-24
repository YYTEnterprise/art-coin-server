<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class Wallet extends Model
{
    protected $fillable = [
        'free_amount',
        'lock_amount',
    ];

    protected $hidden = [
        'id',
        'user_id',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lock($amount) {
        if ($this->free_amount < $amount) {
            throw new BadRequestHttpException('Not enough free balance');
        }

        $this->update([
            'free_amount' => $this->free_amount - $amount,
            'lock_amount' => $this->lock_amount + $amount,
        ]);
    }

    public function unlock($amount) {
        if ($this->lock_amount < $amount) {
            throw new BadRequestHttpException('Not enough lock balance');
        }

        $this->update([
            'free_amount' => $this->free_amount + $amount,
            'lock_amount' => $this->lock_amount - $amount,
        ]);
    }

    public function deposit($amount) {
        $this->update([
            'free_amount' => $this->free_amount + $amount,
        ]);
    }

    public function withdraw($amount) {
        if ($this->free_amount < $amount) {
            throw new BadRequestHttpException('Not enough free balance');
        }

        $this->update([
            'free_amount' => $this->free_amount - $amount,
        ]);
    }

}
