<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderReturn extends Model
{
    public const STATUS_NEW = 1;
    public const STATUS_CONSIDERATION = 2;
    public const STATUS_PROCESSED = 3;
    public const STATUS_REJECTED = 4;

    public const STATUSES = [
        self::STATUS_NEW => 'Новый',
        self::STATUS_CONSIDERATION => 'На рассмотрении',
        self::STATUS_PROCESSED => 'Обработанный',
        self::STATUS_REJECTED => 'Отклоненный',
    ];

    protected $fillable = [
        'order_id',
        'status',
        'reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'order_id' => 'integer',
        'status' => 'integer',
    ];

    /**
     * Order of return
     *
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
