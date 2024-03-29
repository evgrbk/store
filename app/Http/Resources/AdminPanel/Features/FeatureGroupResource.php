<?php

namespace App\Http\Resources\AdminPanel\Features;

use Illuminate\Http\Resources\Json\JsonResource;

class FeatureGroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'features' => $this->features
        ];
    }
}
