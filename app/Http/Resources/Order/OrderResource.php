<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\Order\OrderItemResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'customer_id' => $this->customer_id,
            'status' => $this->status,
            'delivery_type' => $this->delivery_type,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'goods' => OrderItemResource::collection($this->orderGoods),
        ];
    }
}
