<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutoImport extends Model
{
    public const SCHEDULE = [
        self::SCHEDULE_TWICE_A_DAY => 'Дважды в день',
        self::SCHEDULE_DAILY => 'Ежедневно',
        self::SCHEDULE_WEEKLY => 'Еженедельно',
        self::SCHEDULE_MONTHLY => 'Ежемесячно'
    ];

    public const SCHEDULE_HOURS = [
        self::SCHEDULE_TWICE_A_DAY => 11,
        self::SCHEDULE_DAILY => 23,
        self::SCHEDULE_WEEKLY => 167,
        self::SCHEDULE_MONTHLY => 719
    ];

    public const SCHEDULE_TWICE_A_DAY = 1;
    public const SCHEDULE_DAILY = 2;
    public const SCHEDULE_WEEKLY = 3;
    public const SCHEDULE_MONTHLY = 4;

    public const STATUSES = [
        self::STATUS_PENDING => 'Ожидание',
        self::STATUS_IN_PROGRESS => 'В работе',
        self::STATUS_ERROR => 'Ошибка',
    ];

    public const STATUS_PENDING = 1;
    public const STATUS_IN_PROGRESS = 2;
    public const STATUS_ERROR = 3;

    protected $fillable = [
        'schedule',
        'url',
        'active',
        'status',
        'imported_at',
        'parser_type',
        'fields',
        'selected_fields'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'schedule' => 'integer',
        'active' => 'boolean',
        'status' => 'integer',
        'fields' => 'array',
        'selected_fields' => 'array',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'imported_at',
    ];

    /**
     * Returns true if import in progress
     *
     * @return bool
     */
    public function isInProgress(): bool
    {
        return $this->status == self::STATUS_IN_PROGRESS;
    }

    /**
     * Logs of auto import
     *
     * @return HasMany
     */
    public function logs(): HasMany
    {
        return $this->hasMany(AutoImportLog::class);
    }

    /**
     * Auto imports that ready to import
     *
     * @var $query
     * @var int $schedule
     * @return Builder
     */
    public function scopeReadyToImport(Builder $query, int $schedule): Builder
    {
        return $query->where(function ($query) {
            $query->where('status', self::STATUS_PENDING)
                ->orWhere('status', self::STATUS_ERROR);
        })
            ->where('active', 1)
            ->where('schedule', $schedule)
            ->where(function ($query) use ($schedule) {
                $query->whereNull('imported_at')
                    ->orWhere('imported_at', '<=', now()->subHours(AutoImport::SCHEDULE_HOURS[$schedule])->toDateTimeString());
            });
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
}
