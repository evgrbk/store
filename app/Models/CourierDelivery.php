<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourierDelivery extends Model
{
    protected $fillable = [
        'country_id',
        'region_id',
        'limit_sum',
        'cost',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'currency_id' => 'integer',
        'region_id' => 'integer',
        'limit_sum' => 'integer',
        'cost' => 'integer',
    ];

    /**
     * Country relation
     *
     * @return BelongsTo
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Region relation
     *
     * @return BelongsTo
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}
