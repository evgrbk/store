<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public const NAME_PRICING = 'pricing';

    public const DEFAULT_MARGIN = 35;

    protected $fillable = [
        'name',
        'content',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'content' => 'array',
    ];

    /**
     * @return array|null
     */
    public static function getPricing(): ?array
    {
        return optional(self::where('name', self::NAME_PRICING)->first())->content;
    }
}
