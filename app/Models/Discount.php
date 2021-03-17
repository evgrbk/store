<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class Discount extends Model
{
    const TYPE_DISCOUNT = 1;
    const TYPE_MARGIN = 2;

    const VALUE_TYPE_FIX = 1;
    const VALUE_TYPE_PERCENT = 2;

    const TYPES = [
        self::TYPE_DISCOUNT => 'Скидка',
        self::TYPE_MARGIN => 'Наценка'
    ];

    const VALUE_TYPES = [
        self::VALUE_TYPE_FIX => 'Фиксированная',
        self::VALUE_TYPE_PERCENT => 'Процент'
    ];

    protected $fillable = [
        'type',
        'is_active',
        'all_countries',
        'all_brands',
        'all_categories',
        'value_type',
        'value',
        'display',
        'active_till',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'type' => 'integer',
        'is_active' => 'boolean',
        'all_countries' => 'boolean',
        'all_brands' => 'boolean',
        'all_categories' => 'boolean',
        'value_type' => 'integer',
        'value' => 'integer',
        'display' => 'boolean',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'active_till',
    ];

    /**
     * Country pivot table
     *
     * @return BelongsToMany
     */
    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'country_discount');
    }

    /**
     * Brand pivot table
     *
     * @return BelongsToMany
     */
    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class, 'brand_discount');
    }

    /**
     * Category pivot table
     *
     * @return BelongsToMany
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_discount');
    }

    /**
     * List of available discounts
     *
     * @return array|null
     */
    public static function getAvailableList(): ?Collection
    {
        static $discounts = null;
        if (is_null($discounts)) {
            $discounts = Discount::where('is_active', 1)->with('countries', 'brands', 'categories')->get();
        }
        return $discounts;
    }

}
