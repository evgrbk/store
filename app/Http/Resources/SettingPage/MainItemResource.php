<?php

namespace App\Http\Resources\SettingPage;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed id
 * @property mixed title
 * @property mixed description
 */
class MainItemResource extends JsonResource
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
            'image' => [
                'conversions' => $this->conversions,
                'img_original' => $this->img_original,
            ],
        ];
    }
}
