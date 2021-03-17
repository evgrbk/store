<?php

namespace App\Http\Resources\SettingPage;

use App\Http\Resources\ImageResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed title
 * @property mixed description
 * @property mixed conversions
 * @property mixed img_original
 */
class PageSettingItemsResource extends JsonResource
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
            'description' => $this->description,
            'images' => isset($this->images) ? ImageResource::collection($this->images) : [],
            'link' => $this->link,
            'link_text' => $this->link_text,
        ];
    }
}
