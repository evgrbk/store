<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Image\Exceptions\InvalidManipulation;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class MainItem extends Model implements HasMedia
{
    use InteractsWithMedia;

    public const BRAND_TYPE = 'brand';
    public const PAYMENT_TYPE = 'payment';

    protected $table = 'main_page_items';

    protected $fillable = [
        'title',
        'description',
        'link',
        'link_text',
        'page_settings_id',
        'type'
    ];

    /**
     * @return HasMany
     */
    public function images()
    {
        return $this->morphMany(Media::class, 'model');
    }

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
     * Return namespace of the class
     *
     * @return string
     */
    public function namespace(): string
    {
        return __CLASS__;
    }
}
