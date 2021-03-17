<?php

namespace App\Http\Resources\AdminPanel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
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
            'name' => $this->name,
            'currency' => CurrencyResource::make($this->currency),
            'language' => LanguageResource::make($this->language),
            'updated_at' => $this->updated_at,
        ];
    }
}
