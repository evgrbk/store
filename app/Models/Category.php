<?php

namespace App\Models;

use App\Models\Good;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Category extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'title',
        'category_id',
        'seo_h1',
        'seo_title',
        'seo_description',
        'seo_slug',
        'order',
        'features_group_id',
        'weight'
    ];

    /**
     * Category relation
     *
     * @return HasOne
     */
    public function category(): hasOne
    {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }

    /**
     * Category parent - children
     *
     * @return HasMany
     */
    public function categoryInverse(): HasMany
    {
        return $this->hasMany(Category::class, 'category_id', 'id');
    }

    public function goods()
    {
        return $this->hasMany(Good::class);
    }

    public function featureGroup()
    {
        return $this->belongsTo(FeatureGroup::class, 'features_group_id');
    }

    /**
     * Weight sum of category
     *
     * @return float
     */
    /*public function weight(): float
    {
        return $this->goods->sum('weight');
    }*/
}
