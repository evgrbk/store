<?php

namespace App\Http\Resources\SettingPage;

use App\Http\Resources\ImageResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed title_config
 * @property mixed config
 * @property mixed seo_h1
 * @property mixed seo_title
 * @property mixed seo_description
 */
class PageSettingResource extends JsonResource
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
            'title_setting' => $this->title_setting,
            'seo_h1' => $this->seo_h1,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            'config' => $this->config,
            'images' => isset($this->images) ? MainItemResource::collection($this->images) : [],
            'items' => isset($this->items) ? PageSettingItemsResource::collection($this->items) : [],           
            'brand_images' => isset($this->brands['images']) ? ImageResource::collection($this->brands['images']) : [],
            'payment_images' => isset($this->payments['images']) ? ImageResource::collection($this->payments['images']) : [],
        ];
    }
}
