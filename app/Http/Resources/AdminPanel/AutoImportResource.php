<?php

namespace App\Http\Resources\AdminPanel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AutoImportResource extends JsonResource
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
            'schedule' => $this->schedule,
            'url' => $this->url,
            'active' => $this->active,
            'status' => $this->status,
            'parser_type' => $this->parser_type,
            'fields' => $this->fields,
            'selected_fields' => $this->selected_fields,
            'imported_at' => $this->imported_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
