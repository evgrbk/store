<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodRating extends Model
{
    protected $fillable = [
        'good_id',
        'customer_id',
        'value',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'good_id' => 'integer',
        'customer_id' => 'integer',
        'value' => 'integer',
    ];

    /**
     * Return good
     *
     * @return belongsTo
     */
    public function good(): belongsTo
    {
        return $this->belongsTo(Good::class);
    }

    /**
     * Return customer
     *
     * @return belongsTo
     */
    public function customer(): belongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
