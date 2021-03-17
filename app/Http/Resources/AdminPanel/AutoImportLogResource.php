<?php

namespace App\Http\Resources\AdminPanel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AutoImportLogResource extends JsonResource
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
            'auto_import_id' => $this->auto_import_id,
            'status' => $this->status,
            'message' => $this->message,
            'created_at' => $this->created_at,
        ];
    }
}
