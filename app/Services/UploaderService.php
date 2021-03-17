<?php

namespace App\Services;

use App\Models\Media;
use App\Repositories\MediaRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class UploaderService
 *
 * This class is created to work with laravel-medialibrary
 * https://github.com/spatie/laravel-medialibrary
 *
 * @package App\Services
 */
class UploaderService
{
    /**
     * @var MediaRepository
     */
    private MediaRepository $mediaRepository;

    /**
     * UploaderService constructor.
     * @param MediaRepository $mediaRepository
     */
    public function __construct(MediaRepository $mediaRepository)
    {
        $this->mediaRepository = $mediaRepository;
    }

    /**
     * Save file
     *
     * @param array $files
     * @param Model $model
     * @return array
     */
    public function upload(array $files, Model $model): array
    {
        $data = [];

        // handle each received file
        foreach ($files as $file) {
            if (in_array($file->getClientMimeType(), $this->getImageMimeTypes())) {
                // handle images uploading
                $originalName = $file->getClientOriginalName();

                // to get extension
                $image_extension = pathinfo($originalName, PATHINFO_EXTENSION);

                $collectionType = Media::IMAGE_TYPE;

                $randomTitle = md5(Str::random(7)) . microtime() . '.' . $image_extension;
            } elseif (in_array($file->getClientMimeType(), $this->getFilesMimeTypes())) {
                // handle files uploading
                $originalName = $file->getClientOriginalName();

                // to get extension
                $file_extension = pathinfo($originalName, PATHINFO_EXTENSION);

                $collectionType = Media::FILE_TYPE;

                $randomTitle = $originalName;
            } else {
                throw new BadRequestHttpException('MIME TYPE NOT FOUND');
            }

            // save media files
            $data[] = $model->addMedia($file->getRealPath())
                ->usingFileName($randomTitle)
                ->toMediaCollection($collectionType);
        }

        return $data;
    }

    /**
     * To delete existing media image file
     *
     * @param Model $model
     * @param string $type
     * @return bool
     * @throws Exception
     */
    public function delete(Model $model, string $type): bool
    {
        $media = $this->mediaRepository
            ->getAllMediaWithType($model, $type);

        foreach ($media as $file) {
            $this->mediaRepository
                ->delete($file);
        }

        return true;
    }

    /**
     * Delete one media file
     *
     * @param Model $model
     * @param string $type
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function deleteFile(Model $model, string $type, int $id): bool
    {
        $media = $this->mediaRepository
            ->getAllMediaWithTypeById($model, $type, $id);

        if ($media) {
            $this->mediaRepository
                ->delete($media);
        }

        return true;
    }

    /**
     * Return an array of mime types for images
     *
     * @return array
     */
    public function getImageMimeTypes(): array
    {
        return [
            'image/png',
            'image/jpeg',
            'image/gif',
            'image/psd',
            'image/bmp'
        ];
    }

    /**
     * Return an array of mime types for files
     *
     * @return array
     */
    public function getFilesMimeTypes(): array
    {
        // if you change something here,
        // don't forget about getImagesMimeTypesString function

        return [
            'text/plain',
            'application/vnd.ms-excel',
            'text/vnd.ms-word',
            'application/zip',
            'application/pdf',
        ];
    }

    /**
     * Return a string of mime types for files
     *
     * @return string
     */
    public function getFilesMimeTypesString(): string
    {
        return 'txt,xls,xlsx,zip,pdf,doc,docx';
    }

    /**
     * Return a string of mime types for images
     *
     * @return string
     */
    public function getImagesMimeTypesString(): string
    {
        $string = '';
        foreach ($this->getImageMimeTypes() as $type) {
            $type = explode('/', $type)[1];
            $string .= $type . ',';
        }
        $string = rtrim($string, ',');

        return $string;
    }
}
