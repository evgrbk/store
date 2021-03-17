<?php

namespace App\Repositories;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class MediaRepository
{
    /**
     * Get all media files
     *
     * @param Model $model
     * @param string $type
     * @return array
     */
    public function getAllMediaWithType(Model $model, string $type): array
    {
        return $model->getMedia($type)
            ->all();
    }

    /**
     * Get all media files
     *
     * @param Model $model
     * @param string $type
     * @param int $id
     * @return Model
     */
    public function getAllMediaWithTypeById(Model $model, string $type, int $id): ?Model
    {
        return $model->getMedia($type)
            ->where('id', $id)
            ->first();
    }

    /**
     * Delete media file for model
     *
     * @param Model $model
     * @return bool
     * @throws Exception
     */
    public function delete(Model $model): bool
    {
        return $model->delete();
    }
}
