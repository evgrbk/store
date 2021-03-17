<?php

namespace App\Http\Resources\AdminPanel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GoodTranslateResource extends JsonResource
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
            'good_id' => $this->good_id,
            'language_id' => $this->language_id,
            'good_title' => $this->good_title,
            'good_description' => $this->good_description,
            'language_name' => $this->language->name,
        ];
    }
}
