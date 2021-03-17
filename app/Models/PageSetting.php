<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Image\Exceptions\InvalidManipulation;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PageSetting extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'title_setting',
        'config',
        'seo_h1',
        'seo_title',
        'seo_description',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    /**
     * Media Library function to optimize images
     *
     * @param \Spatie\MediaLibrary\MediaCollections\Models\Media|null $media
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

    /**
     * Return has many relation with Media
     *
     * @return HasMany
     */
    public function images(): HasMany
    {
        return $this->hasMany(Media::class, 'model_id', 'id');
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

    /**
     * @return HasMany
     */
    public function items()
    {
        return $this->hasMany(MainItem::class, 'page_settings_id', 'id')
            ->whereNull('type');
    }

    /**
     * @return HasMany
     */
    public function brands(): HasMany
    {
        return $this->hasMany(MainItem::class, 'page_settings_id', 'id')
            ->where('type', MainItem::BRAND_TYPE);
    }


    /**
     * @return HasMany
     */
    public function payments(): HasMany
    {
        return $this->hasMany(MainItem::class, 'page_settings_id', 'id')
            ->where('type', MainItem::PAYMENT_TYPE);
    }
}
