<?php

namespace App\Http\Resources\AdminPanel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\AdminPanel\CurrencyRateResource;

class CountryResourceCollection extends ResourceCollection
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
            'data' => parent::toArray($request),
            'count' => $this->total(),
        ];
    }
}
