<?php

namespace App\Http\Resources\AdminPanel;

use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyResource extends JsonResource
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
            'name' => $this->name,
            'code' => $this->code,
            'symbol' => $this->symbol,
            'nominal' => $this->nominal,
            'primary' => $this->primary,
            'updated_at' => $this->updated_at,
        ];
    }
}
