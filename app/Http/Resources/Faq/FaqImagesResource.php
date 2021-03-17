<?php

namespace App\Http\Resources\Faq;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed id
 * @property mixed conversions
 * @property mixed img_original
 */
class FaqImagesResource extends JsonResource
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
            'image' => [
                'conversions' => $this->conversions,
                'img_original' => $this->img_original,
            ],
        ];
    }
}
