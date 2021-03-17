<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    const DELIVERY_TYPES = [
        self::DELIVERY_TYPE_FREE,
        self::DELIVERY_TYPE_COURIER
    ];

    const STATUSES = [
        self::STATUS_PENDING,
    ];

    const DELIVERY_TYPE_FREE = 0;
    const DELIVERY_TYPE_COURIER = 1;
    const STATUS_PENDING = 1;

    protected $fillable = [
        'customer_id',
        'guest_hash',
        'status',
        'delivery_type',
        'full_name',
        'email',
        'phone',
        'address'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'customer_id' => 'integer',
        'status' => 'integer',
        'delivery_type' => 'integer',
    ];

    /**
     * Order items
     *
     * @return HasMany
     */
    public function orderGoods()
    {
        return $this->hasMany(OrderGood::class);
    }

    /**
     * Order customer
     *
     * @return BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
