<?php

namespace App\Http\Resources\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\GoodResourceGeneral;

class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'good_id' => $this->good_id,
            'count' => $this->count,
            'discount' => $this->discount,
            'sum' => $this->sum(),
            'good' => new GoodResourceGeneral($this->good)
        ];
    }
}
