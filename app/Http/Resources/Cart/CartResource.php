<?php

namespace App\Http\Resources\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
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
            'auth' => $this->customer_id ? true : false,
            'guest_hash' => $this->guest_hash ?? null,
            'cart_sum' => $this->cartSum,
            'cart_items' => CartItemResource::collection($this->cartItems),
        ];
    }
}
