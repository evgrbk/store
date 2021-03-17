<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    public const STATUS_REQUESTED = 'requested';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SUCCEEDED = 'succeeded';
    public const STATUS_EXPIRED = 'expired';
    public const STATUSES = [
        self::STATUS_REQUESTED,
        self::STATUS_SUCCEEDED,
        self::STATUS_FAILED,
        self::STATUS_EXPIRED
    ];

    public const CURRENCY_TYPE_RUS = 643;

    protected $fillable = [
        'amount',
        'penny',
        'comment',
        'status',
        'currency',
        'payment_service_id',
        'email',
        'total_sum',
        'raw',
        'external_status',
        'uuid',
        'additional_data',
        'expires_at'
    ];

    protected $casts = [
        'raw' => 'json',
        'additional_data' => 'json'
    ];

    /**
     * @return HasOne
     */
    public function payment()
    {
        return $this->hasOne(PaymentService::class, 'id', 'payment_service_id');
    }

    public function goods()
    {
        return $this->belongsToMany(
            Good::class,
            'goods_payments',
            'payment_id',
            'good_id',
            'id'
        )
        ->withPivot('quantity');
    }
}
