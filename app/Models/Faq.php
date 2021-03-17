<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @method static create(array $data)
 * @method static where(string $string, int $id)
 * @method static find(int $id)
 * @method findOrFail(int $id)
 */
class Faq extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'faq';

    protected $fillable = [
        'question',
        'answer',
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
    public function images()
    {
        return $this->hasMany(Media::class, 'model_id', 'id');
    }

}
