<?php

namespace App\Http\Resources\AdminPanel\PaymentService;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed service_title
 * @property mixed data
 * @property mixed id
 */
class CreatePaymentServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'service_title' => $this->service_title,
            'data' => $this->data,
        ];
    }
}
