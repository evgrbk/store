<?php

namespace App\Services;

use App\Adapters\MediaConversionsAdapter;
use App\Repositories\GoodRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Repositories\BrandRepository;
use Illuminate\Support\Collection;

use Exception;

class BrandService extends Service
{
    /**
     * @var BrandRepository
     */
    protected $brandRepository;

    /**
     * @var GoodRepository
     */
    protected $goodRepository;

    /**
     * @var UploaderService
     */
    private UploaderService $uploaderService;

    /**
     * @var MediaConversionsAdapter
     */
    private MediaConversionsAdapter $mediaConversionsAdapter;

    /**
     * BrandService constructor.
     *
     * @param UploaderService $uploaderService
     * @param BrandRepository $brandRepository
     * @param GoodRepository $goodRepository
     * @param MediaConversionsAdapter $mediaConversionsAdapter
     */
    public function __construct(UploaderService $uploaderService,
                                BrandRepository $brandRepository,
                                GoodRepository $goodRepository,
                                MediaConversionsAdapter $mediaConversionsAdapter)
    {
        $this->uploaderService = $uploaderService;
        $this->brandRepository = $brandRepository;
        $this->goodRepository = $goodRepository;
        $this->mediaConversionsAdapter = $mediaConversionsAdapter;
    }

    /**
     * Get brands
     *
     * @param int $limit
     * @return LengthAwarePaginator
     */
    public function getList(int $limit): LengthAwarePaginator
    {
        $brands = $this->brandRepository
            ->paginateAll($limit);

        return $brands;
    }

    /**
     * Get brand
     *
     * @param int $id
     * @return Model
     */
    public function getOne(int $id): Model
    {
        $model = $this->brandRepository
            ->getRecord($id);

        return $model;
    }

    /**
     * Create or update brand
     *
     * @param array $data
     * @param int $id
     * @return Model
     * @throws Exception
     */
    public function createOrUpdate(array $data, int $id = 0): Model
    {
        try {
            DB::beginTransaction();

            if ($id) {
                $model = $this
                    ->brandRepository
                    ->update($data, $id);
            } else {
                $model = $this->brandRepository->store($data);
            }

            if (isset($data['img'])) {
                $model->clearMediaCollection('images');

                $this->uploaderService
                    ->upload([$data['img']], $model);
            }

            DB::commit();

            return $model;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Delete brand
     *
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function destroy(int $id): bool
    {
        if ($this->goodRepository->whereCount('brand_id', $id)) {
            return false;
        } else {
            return $this->brandRepository
                ->destroy($id);
        }
    }

    /**
     * Find brand
     *
     * @param array $data
     * @return Collection
     */
    public function findBrand(array $data): Collection
    {
        if (isset($data['name'])) {
            $model = $this->brandRepository
                ->findByName($data);
        } else {
            $model = $this->brandRepository
                ->all();
        }

        return $model;
    }

    /**
     * Get all names
     *
     * @return Collection
     */
    public function getNames(): Collection
    {
        return $this->brandRepository
            ->allColumns(['id', 'name']);
    }

}
