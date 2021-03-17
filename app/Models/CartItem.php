<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'good_id',
        'count',
        'discount',
    ];

    /**
     * Cart items
     *
     * @return HasOne
     */
    public function good(): HasOne
    {
        return $this->hasOne(Good::class, 'id', 'good_id');
    }

    /**
     * Item sum
     *
     * @return float
     */
    public function sum(): float
    {
        return $this->good->price * $this->count;
    }
}
