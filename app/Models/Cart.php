<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = [
        'customer_id',
        'guest_hash',
    ];

    /**
     * Cart items
     *
     * @return HasMany
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'cart_id', 'id');
    }

    /**
     * Sum cart items
     *
     * @return float
     */
    public function cartSum(): float
    {
        $sum = 0;
        $items = $this->cartItems()->with('good')->get();
        foreach ($items as $item) {
            $sum += $item->sum();
        }
        return $sum;
    }
}
