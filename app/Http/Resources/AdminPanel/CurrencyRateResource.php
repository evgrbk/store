<?php

namespace App\Http\Resources\AdminPanel;

use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyRateResource extends JsonResource
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
            'currency_id' => $this->currency_id,
            'currency_name' => $this->currency->name,
            'currency_code' => $this->currency->code,
            'type' => $this->type,
            'rate' => $this->rate,
            'rate_updated_at' => $this->rate_updated_at,
            'limit' => $this->limit,
            'interval' => $this->interval,
            'updated_at' => $this->updated_at,
        ];
    }
}
