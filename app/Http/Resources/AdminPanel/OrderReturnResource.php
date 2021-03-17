<?php

namespace App\Http\Resources\AdminPanel;

use App\Http\Resources\Order\OrderItemResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderReturnResource extends JsonResource
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
            'order_id' => $this->order_id,
            'status' => $this->status,
            'reason' => $this->reason,
            'fio' => $this->order->full_name,
            'email' => $this->order->email,
            'phone' => $this->order->phone,
            'created_at' => $this->created_at,
        ];
    }
}
