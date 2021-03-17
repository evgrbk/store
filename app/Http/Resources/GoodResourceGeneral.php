<?php

namespace App\Http\Resources;

use App\Http\Resources\FileResourceGeneral;
use App\Http\Resources\ImageResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\AdminPanel\CategoryResource;

class GoodResourceGeneral extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'good_title' => $this->getTranslatedTitle(),
            'good_description' => $this->getTranslatedDescription(),
            'category_id' => $this->category_id,
            'brand_id' => $this->brand_id,
            'sku' => $this->sku,
            'seo_h1' => $this->seo_h1,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            'seo_slug' => $this->seo_slug,
            'active' => $this->active,
            'good_left' => $this->good_left,
            'good_type' => $this->good_type,
            'price' => $this->price,
            'rating' => $this->rating,
            'weight' => $this->weight,
            'category_weight' => optional($this->category)->weight,
            'is_favorite' => $this->when(auth()->guard('customers')->check(), $this->is_favorite),
            'features' => $this->features,
            'images' => isset($this->images) ? ImageResource::collection($this->images) : [],
            'category' => isset($this->category) ? CategoryResource::make($this->category) : [],
            'brand' => $this->brand,
        ];
    }
}
