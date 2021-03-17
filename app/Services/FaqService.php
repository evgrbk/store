<?php

namespace App\Services;

use App\Adapters\MediaConversionsAdapter;
use App\Models\Media;
use App\Repositories\FaqRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Exception;
use Illuminate\Support\Facades\DB;

class FaqService
{
    /**
     * @var FaqRepository
     */
    protected $faq_repository;

    /**
     * @var UploaderService
     */
    protected $uploaderService;

    /**
     * @var MediaConversionsAdapter
     */
    public MediaConversionsAdapter $mediaConversionsAdapter;

    /**
     * FaqService constructor.
     *
     * @param FaqRepository $repository
     * @param UploaderService $uploaderService
     * @param MediaConversionsAdapter $mediaConversionsAdapter
     */
    public function __construct(
        FaqRepository $repository,
        UploaderService $uploaderService,
        MediaConversionsAdapter $mediaConversionsAdapter
    ) {
        $this->faq_repository = $repository;
        $this->uploaderService = $uploaderService;
        $this->mediaConversionsAdapter = $mediaConversionsAdapter;
    }

    /**
     * @param int $limit
     * @return LengthAwarePaginator
     */
    public function getAllFaq(int $limit): LengthAwarePaginator
    {
        $faqs = $this->faq_repository
        ->paginateAllWithRelation($limit);

        foreach ($faqs as $faq) {
            $this
                ->mediaConversionsAdapter
                ->adaptImages($faq);
        }

        return $faqs;
    }

    /**
     * Create new faq
     *
     * @param array $data
     * @return Model
     */
    public function createNewFaq(array $data): Model
    {
        $model = $this->faq_repository
            ->store($data);

        if (isset($data['img'])) {
            $this->uploaderService
                ->upload($data['img'] , $model);
        }

        $this
            ->faq_repository
            ->getWith($model, $model->id, 'images');

        $this->mediaConversionsAdapter->adaptImages($model);

        return $model;
    }

    /**
     * Update one faq by id
     *
     * @param int $id
     * @param array $data
     * @return Model
     * @throws Exception
     */
    public function updateFaq(int $id, array $data): Model
    {
        try {
            DB::beginTransaction();

            $model = $this
                ->faq_repository
                ->update($data, $id);

            // delete files
            if (isset($data['deleted_files'])) {
                $this->deleteFiles($model, $data);
            }

            $this->handleFiles($data, $model);

            DB::commit();

            return $model;

        } catch (Exception $e) {

            DB::rollBack();

            throw new Exception($e->getMessage());

        }

    }

    /**
     * Get faq by id
     *
     * @param int $id
     * @return Model
     */
    public function getFaq(int $id): Model
    {
        $model = $this->faq_repository
            ->getRecord($id);

        $this->mediaConversionsAdapter
            ->adaptImages($model);

        return $model;
    }

    /**
     * Delete one faq record
     *
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function destroyOneFaq(int $id): bool
    {
        $model = $this
            ->faq_repository
            ->getRecord($id);

        $this
            ->uploaderService
            ->delete($model, Media::FILE_TYPE);
        $this
            ->uploaderService
            ->delete($model, Media::IMAGE_TYPE);

        return $this->faq_repository
            ->destroy($id);
    }

    /**
     * upload image
     *
     * @param array $data
     * @param Model $model
     */
    private function handleFiles(array $data, model $model)
    {

        if (isset($data['img'])) {
            // make arrays with images because UploaderService takes an array
            // if we get just 1 image we need to make an array anyway
            $images = is_array($data['img']) ? $data['img'] : [$data['img']];
            $this->uploaderService
                ->upload($images, $model);
        }
    }

    /**
     * @param $model
     * @param array $data
     * @throws Exception
     */
    protected function deleteFiles($model, array $data)
    {
        foreach ($data['deleted_files'] as $file_id) {
            if (!$file_id) {
                continue;
            }
            $this
                ->uploaderService
                ->deleteFile($model, Media::IMAGE_TYPE, $file_id);
            $this
                ->uploaderService
                ->deleteFile($model, Media::FILE_TYPE, $file_id);
        }
    }
}
