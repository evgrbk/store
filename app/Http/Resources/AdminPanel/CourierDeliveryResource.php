<?php

namespace App\Http\Resources\AdminPanel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourierDeliveryResource extends JsonResource
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
            'country_id' => $this->country_id,
            'country_name' => optional($this->country)->name,
            'region_id' => $this->region_id,
            'region_name' => optional($this->region)->name,
            'limit_sum' => $this->limit_sum,
            'cost' => $this->cost,
            'updated_at' => $this->updated_at,
        ];
    }
}
