<?php

namespace App\Http\Resources\AdminPanel;

use Illuminate\Http\Resources\Json\JsonResource;

class DiscountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'is_active' => $this->is_active,
            'all_countries' => $this->all_countries,
            'all_brands' => $this->all_brands,
            'all_categories' => $this->all_categories,
            'countries' => CountryNameResource::collection($this->countries),
            'brands' => BrandNameResource::collection($this->brands),
            'categories' => CategoryNameResource::collection($this->categories),
            'value_type' => $this->value_type,
            'value' => $this->value,
            'display' => $this->display,
            'active_till' => $this->active_till,
            'updated_at' => $this->updated_at,
        ];
    }
}
