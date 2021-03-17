<?php

namespace App\Http\Resources\AdminPanel;

use App\Http\Resources\FileResource;
use App\Http\Resources\ImageResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GoodResource extends JsonResource
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
            'good_title' => $this->good_title,
            'good_description' => $this->good_description,
            'category_id' => $this->category_id,
            'brand_id' => $this->brand_id,
            'sku' => $this->sku,
            'weight' => $this->weight,
            'category_weight' => optional($this->category)->weight,
            'seo_h1' => $this->seo_h1,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            'seo_slug' => $this->seo_slug,
            'active' => $this->active,
            'good_left' => $this->good_left,
            'good_type' => $this->good_type,
            'price_integer' => $this->price_integer,
            'price_decimal' => $this->price_decimal,
            'files' => isset($this->files) ? FileResource::collection($this->files) : [],
            'images' => isset($this->images) ? ImageResource::collection($this->images) : [],
            'category' => isset($this->category) ? CategoryResource::make($this->category) : [],
            'price' => $this->price,
            'features' => $this->features
        ];
    }
}
