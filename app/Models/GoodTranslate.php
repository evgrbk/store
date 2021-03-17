<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodTranslate extends Model
{
    protected $fillable = [
        'good_id',
        'language_id',
        'good_title',
        'good_description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'good_id' => 'integer',
        'language_id' => 'integer',
    ];

    /**
     * Good relation
     *
     * @return BelongsTo
     */
    public function good(): BelongsTo
    {
        return $this->belongsTo(Good::class);
    }

    /**
     * Language relation
     *
     * @return BelongsTo
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
