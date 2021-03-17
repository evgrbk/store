<?php

namespace App\Adapters;

use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MediaConversionsAdapter
{
    /**
     * Adapt naming of converted images
     *
     * @param Model $model
     * @return Model
     */
    public function adaptImages(Model $model): Model
    {
        $conversions = [];

        foreach ($model->images as $image_data) {
//            // handle images
            $conversions_path = Storage::url(config('media.directory_path'))
                . '/'
                . $image_data->id
                . '/conversions/';

            $conversions = [];

            foreach (Media::CONVERSION_TYPES as $conversion_type) {
                $file_name = pathinfo($image_data->file_name, PATHINFO_FILENAME);
                $file_extension = 'jpg';

                $conversions[$conversion_type] = $conversions_path
                    . $file_name
                    . '-'
                    . $conversion_type
                    . '.'
                    . $file_extension;
            }

            $image_data->conversions = $conversions;

            $img_original_path = Storage::url(config('media.directory_path'))
                . '/'
                . $image_data->id
                . '/'
                . $image_data->file_name;

            $image_data->img_original = $img_original_path;
        }

        if (isset($model->files)) {
            foreach ($model->files as $file) {
                // handle files
                $file_original_path = Storage::url(config('media.directory_path'))
                    . '/'
                    . $file->id
                    . '/'
                    . $file->file_name;

                $file->file_original = $file_original_path;
            }
        }

        return $model;
    }
}
