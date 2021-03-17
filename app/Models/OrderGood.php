<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderGood extends Model
{
    protected $fillable = [
        'order_id',
        'good_id',
        'good_price',
        'good_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'order_id' => 'integer',
        'good_id' => 'integer',
        'good_price' => 'decimal:2',
        'good_count' => 'integer',
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
}
