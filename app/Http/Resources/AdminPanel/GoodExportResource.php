<?php

namespace App\Http\Resources\AdminPanel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GoodExportResource extends JsonResource
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
            'category' => isset($this->category) ? $this->category->title : '',
            'brand' => isset($this->brand) ? $this->brand->name : '',
            'sku' => $this->sku,
            'seo_h1' => $this->seo_h1,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            'seo_slug' => $this->seo_slug,
            'active' => $this->active ? 'Активен' : 'Не активен',
            'good_left' => $this->good_left,
            'good_type' => $this->good_type,
            'price' => $this->price
        ];
    }
}
