<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerFavorite extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'good_id',
        'customer_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'good_id' => 'integer',
        'customer_id' => 'integer',
    ];

    /**
     * Customer relation
     *
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Good relation
     *
     * @return BelongsTo
     */
    public function good(): BelongsTo
    {
        return $this->belongsTo(Good::class);
    }
}
