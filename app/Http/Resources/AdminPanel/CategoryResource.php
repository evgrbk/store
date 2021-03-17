<?php

namespace App\Http\Resources\AdminPanel;

use App\Http\Resources\AdminPanel\CategoryWithSubcategoriesResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\AdminPanel\Features\FeatureGroupResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'category_id' => $this->category_id,
            'parent_category' => isset($this->category) ? CategoryResource::make($this->category) : [],
            'child_category' => isset($this->categoryInverse) ? CategoryWithSubcategoriesResource::collection($this->categoryInverse()->orderBy('order')->get()) : [],
            'seo_h1' => $this->seo_h1,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            'seo_slug' => $this->seo_slug,
            'order' => $this->order,
            'weight' => $this->weight,
            'features_group' => new FeatureGroupResource($this->featureGroup)
         ];
    }
}
