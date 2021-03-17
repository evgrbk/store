<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class CurrencyRate extends Model
{
    public const TYPE_MANUAL = 1;
    public const TYPE_AUTO = 2;

    public const INTERVAL_DAY = 1;
    public const INTERVAL_3_DAYS = 3;
    public const INTERVAL_5_DAYS = 5;
    public const INTERVAL_WEEK = 7;

    public const INTERVALS = [
        self::INTERVAL_DAY,
        self::INTERVAL_3_DAYS,
        self::INTERVAL_5_DAYS,
        self::INTERVAL_WEEK,
    ];

    protected $fillable = [
        'currency_id',
        'type',
        'rate',
        'rate_updated_at',
        'limit',
        'interval'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'currency_id' => 'integer',
        'type' => 'integer',
        'rate' => 'decimal:4',
        'limit' => 'integer',
        'interval' => 'integer',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'rate_updated_at',
    ];

    /**
     * Currency relation
     *
     * @return BelongsTo
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Check type is auto
     *
     * @return bool
     */
    public function isAuto(): bool
    {
        return $this->type === self::TYPE_AUTO;
    }

    /**
     * Set rate updated date after update
     *
     * @return void
     * @var float $value
     */
    public function setRateAttribute(float $value): void
    {
        $this->attributes['rate'] = $value;
        if ($this->attributes['type'] == self::TYPE_AUTO) {
            $this->attributes['rate_updated_at'] = now();
        }
    }

    /**
     * Disable timestamps when update
     *
     * @var float $value
     */
    public function scopeWithoutTimestamps()
    {
        $this->timestamps = false;
        return $this;
    }

    /**
     * Add type auto to query
     *
     * @var $query
     * @return Builder
     */
    public function scopeAuto($query): Builder
    {
        return $query->where('type', self::TYPE_AUTO);
    }

}
