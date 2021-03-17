<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Spatie\Image\Exceptions\InvalidManipulation;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Good extends Model implements HasMedia
{
    use InteractsWithMedia;

    public const TYPE_UNLIMITED = 'unlimited';
    public const TYPE_LIMITED = 'limited';

    public const EXPORT_TYPES = ['excel'];

    /**
     * @var array
     */
    protected $fillable = [
        'good_internal_id',
        'good_title',
        'good_description',
        'category_id',
        'brand_id',
        'seo_h1',
        'seo_title',
        'seo_description',
        'seo_slug',
        'active',
        'good_left',
        'good_type',
        'price_integer',
        'price_decimal',
        'sku',
        'weight'
    ];

    protected $casts = [
        'active' => 'boolean',
        'weight' => 'decimal:2'
    ];

    /**
     * Media Library function to optimize images
     *
     * @param \Spatie\MediaLibrary\MediaCollections\Models\Media $media
     * @throws InvalidManipulation
     */
    public function registerMediaConversions(\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        $this->addMediaConversion(Media::CONVERSION_TYPE_SMALL)
            ->width(config('media.images.optimization.resolution.small_width'))
            ->performOnCollections(Media::IMAGE_TYPE);

        $this->addMediaConversion(Media::CONVERSION_TYPE_MIDDLE)
            ->width(config('media.images.optimization.resolution.middle_width'))
            ->performOnCollections(Media::IMAGE_TYPE);

        $this->addMediaConversion(Media::CONVERSION_TYPE_BIG)
            ->width(config('media.images.optimization.resolution.big_width'))
            ->performOnCollections(Media::IMAGE_TYPE);
    }

    public function files()
    {
        return $this->morphMany(Media::class, 'model')
            ->where('collection_name', Media::FILE_TYPE);
    }

    public function images()
    {
        return $this->morphMany(Media::class, 'model')
            ->where('collection_name', Media::IMAGE_TYPE);
    }

    /**
     * Return one category relation
     *
     * @return HasOne
     */
    public function category(): HasOne
    {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }

    /**
     * Return brand relation
     *
     * @return HasOne
     */
    public function brand(): HasOne
    {
        return $this->hasOne(Brand::class, 'id', 'brand_id');
    }

    /**
     * Return good ratings
     *
     * @return HasMany
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(GoodRating::class);
    }

    /**
     * Return avg of ratings
     *
     * @return float
     */
    public function getRatingAttribute(): float
    {
        return round($this->ratings->avg('value'), 1);
    }

    /**
     * Return namespace of the class
     *
     * @return string
     */
    public function namespace(): string
    {
        return __CLASS__;
    }

    public function features()
    {
        return $this->belongsToMany('App\Models\Feature', 'goods_feature', 'goods_id', 'feature_id')
            ->withPivot(['value']);
    }

    /**
     * Translates relation
     *
     * @return HasMany
     */
    public function translates(): HasMany
    {
        return $this->hasMany(GoodTranslate::class);
    }

    /**
     * Get languages array
     *
     * @return array
     */
    private function getLanguages(): array
    {
        static $primary, $languages = null;
        if (is_null($primary)) {
            $primary = optional(Language::where('primary', 1)->first())->code;
        }
        if (is_null($languages)) {
            $languages = Language::pluck('id', 'code')->toArray();
        }
        return [
            'primary' => $primary,
            'languages' => $languages,
        ];
    }

    /**
     * Get translated title
     *
     * @param string|null $lang
     * @return string
     */
    public function getTranslatedTitle(?string $lang = ''): string
    {
        // If language code is not specified, get primary language code
        if (empty($lang)) {
            // If primary language code not found, just return original title
            if (empty($lang = \Arr::get($this->getLanguages(), 'primary'))) {
                return $this->good_title;
            }
        }
        // If language id not found, just return original title
        if (empty($langId = \Arr::get($this->getLanguages(), 'languages.' . $lang))) {
            return $this->good_title;
        }
        // If translated title not found, just return original title
        $translatedTitle = optional($this->translates->where('language_id', $langId)->first())->good_title;
        return $translatedTitle ?? $this->good_title;
    }

    /**
     * Get translated description
     *
     * @param string|null $lang
     * @return string
     */
    public function getTranslatedDescription(?string $lang = ''): string
    {
        // If language code is not specified, get primary language code
        if (empty($lang)) {
            // If primary language code not found, just return original description
            if (empty($lang = \Arr::get($this->getLanguages(), 'primary'))) {
                return $this->good_description;
            }
        }
        // If language id not found, just return original description
        if (empty($langId = \Arr::get($this->getLanguages(), 'languages.' . $lang))) {
            return $this->good_description;
        }
        // If translated description not found, just return original description
        $translatedDescription = optional($this->translates->where('language_id', $langId)->first())->good_description;
        return $translatedDescription ?? $this->good_description;
    }


    /**
     * Get favorites of customer
     *
     * @return Collection|null
     */
    private static function getCustomerFavorites(): ?Collection
    {
        static $favourites = null;
        if (is_null($favourites)) {
            $favourites = CustomerFavorite::where('customer_id', auth()->guard('customers')->id())->get();
        }
        return $favourites;
    }

    /**
     * Is good favorite
     *
     * @return bool
     */
    public function getIsFavoriteAttribute(): bool
    {
        return self::getCustomerFavorites()->where('good_id', $this->id)->count();
    }
}
