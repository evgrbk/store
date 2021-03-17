<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            'email' => $this->email,
            'phone' => $this->phone,
            'full_name' => $this->full_name,
            'dob' => $this->dob,
            'is_male' => $this->is_male,
            'is_active' => $this->is_active,
            'updated_at' => $this->updated_at,
        ];
    }
}
