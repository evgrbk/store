<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\GoodResourceGeneral;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
            'good_id' => $this->order_id,
            'good_price' => $this->good_price,
            'good_count' => $this->good_count,
            'good' => GoodResourceGeneral::make($this->good),
        ];
    }
}
