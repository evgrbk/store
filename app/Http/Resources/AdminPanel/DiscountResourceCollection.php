<?php

namespace App\Http\Resources\AdminPanel;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DiscountResourceCollection extends ResourceCollection
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
            'data' => parent::toArray($request),
            'count' => $this->total(),
        ];
    }
}
