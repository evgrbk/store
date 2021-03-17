<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryWithSubcategoriesResource extends JsonResource
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
            'child_category' => isset($this->categoryInverse) ? CategoryWithSubcategoriesResource::collection($this->categoryInverse()->orderBy('order')->get()) : [],
            'goods_count' => $this->goods()->where('active', 1)->count(),
            'seo_h1' => $this->seo_h1,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            'seo_slug' => $this->seo_slug
        ];
    }
}
