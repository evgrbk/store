<?php

namespace App\Repositories;

use App\Models\PageSetting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class PageSettingRepository extends Repository
{

    /**
     * @var MainItemRepository
     */
    protected MainItemRepository $mainItemRepository;

    /**
     * PageSettingRepository constructor.
     * @param MainItemRepository $mainItemRepository
     */
    public function __construct(MainItemRepository $mainItemRepository)
    {
        $this->model = new PageSetting();
        $this->mainItemRepository = $mainItemRepository;
    }

    /**
     * Create new setting
     *
     * @param array $data
     * @return Model
     */
    public function store(array $data): Model
    {
        return $this->model()
            ->create($data);
    }

    /**
     * Update record by id
     *
     * @param array $data
     * @param int $id
     * @return Model
     */
    public function update(array $data, int $id): Model
    {
        $model = $this->model
            ->find($id);

        $model->update($data);

        return $model;
    }

    public function updateByPage(array $data, string $page): Model
    {
        $model = $this->model
            ->where('title_setting', $page)
            ->first();

        if (!$model) {
            return $this->model()
            ->create($data);
        }

        $model->update($data);

        return $model;

    }

    /**
     * Return one record by id
     *
     * @param $id
     * @return mixed
     */
    public function getRecord(int $id): Model
    {
        $namespace = $this->model
            ->namespace();

        return $this->model
            ->with(['items.images' => function ($q) {
                $q->where('media.model_type', $this
                    ->mainItemRepository
                    ->model
                    ->namespace());
            }])
            ->with(['brands.images' => function ($q) {
                $q->where('media.model_type', $this
                    ->mainItemRepository
                    ->model
                    ->namespace());
            }])
            ->with(['payments.images' => function ($q) {
                $q->where('media.model_type', $this
                    ->mainItemRepository
                    ->model
                    ->namespace());
            }])
            ->with(['images' => function ($q) use ($namespace) {
                $q->where('media.model_type', $namespace);
            }])
            ->findOrFail($id);
    }

    public function getRecordByPage(string $page): ?Model
    {
        $namespace = $this->model
            ->namespace();

        return $this->model
            ->with(['items.images' => function ($q) {
                $q->where('media.model_type', $this
                    ->mainItemRepository
                    ->model
                    ->namespace());
            }])
            ->with(['images' => function ($q) use ($namespace) {
                $q->where('media.model_type', $namespace);
            }])
            ->where('title_setting', $page)
            ->first();
    }

    /**
     * Get record with relations
     *
     * @param int $id
     * @return Model
     */
    public function getRecordWith(int $id): Model
    {
        return $this->model
            ->where('id', $id)
            ->with('items')
            ->first();
    }

    /**
     * Destroy one record by id
     *
     * @param $id
     * @return bool
     */
    public function destroy(int $id): bool
    {
        return $this->model
            ->destroy($id);
    }

    /**
     * Get all records with pagination
     *
     * @param int $limit
     * @return LengthAwarePaginator
     */
    public function paginateAllWithRelation(int $limit): LengthAwarePaginator
    {
        $namespace = $this->model
            ->namespace();

        return $this
            ->model
            ->with(['items.images' => function ($q) {
                $q->where('media.model_type', $this
                    ->mainItemRepository
                    ->model
                    ->namespace());
            }])
            ->with(['brands.images' => function ($q) {
                $q->where('media.model_type', $this
                    ->mainItemRepository
                    ->model
                    ->namespace());
            }])
            ->with(['payments.images' => function ($q) {
                $q->where('media.model_type', $this
                    ->mainItemRepository
                    ->model
                    ->namespace());
            }])
            ->with(['images' => function ($q) use ($namespace) {
                $q->where('media.model_type', $namespace);
            }])
            ->paginate($limit);
    }
}
