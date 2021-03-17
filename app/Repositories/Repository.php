<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class Repository implements RepositoryInterface
{
    /**
     * @var
     */
    public $model;

    /**
     * Get new model
     *
     * @return model
     */
    public function model(): model
    {
        return $this
            ->model;
    }

    /**
     * Get all records
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return $this
            ->model
            ->all();
    }

    /**
     * Count of all records
     *
     * @return int
     */
    public function countAll(): int
    {
        return $this
            ->model
            ->count();
    }

    /**
     * Get columns
     *
     * @return Collection
     */
    public function allColumns($columns = ['*']): Collection
    {
        return $this
            ->model
            ->get($columns);
    }

    /**
     * Get all records with pagination
     *
     * @param int $limit
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function paginateAll(int $limit, array $params = []): LengthAwarePaginator
    {
        $model = $this
            ->model;

        if (isset($params['orderBy']) && isset($params['ascending'])) {
            $model = $model->orderBy($params['orderBy'], $params['ascending'] ? 'ASC' : 'DESC');
        };

        return $model->paginate($limit);
    }

    /**
     * To save a new record
     *
     * @param array $data
     * @return Model
     */
    public function store(array $data): Model
    {
        return $this->model
            ->newQuery()
            ->create($data);
    }

    /**
     * Update record
     *
     * @param array $data
     * @param $id
     * @return Model
     */
    public function update(array $data, int $id): Model
    {
        $model = $this->model
            ->find($id);

        $model->update($data);

        return $model;
    }

    /**
     * Delete record
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
     * Save Model
     *
     * @param Model $model
     * @return bool
     */
    public function saveModel(Model $model): bool
    {
        return $model->save();
    }

    /**
     * Get one good by id with media
     *
     * @param int $id
     * @return Model
     */
    public function getRecord(int $id): ?Model
    {
        return $this->model
            ->findOrFail($id);
    }

    /**
     * @param array $ids
     * @return Collection
     */
    public function getRecordsByIds(array $ids): Collection
    {
        return $this
            ->model
            ->whereIn('id', $ids)
            ->get();
    }

    /**
     * Get with relation
     *
     * @param Model $model
     * @param int $id
     * @param string $with
     * @return Model
     */
    public function getWith(Model $model, int $id, string $with): Model
    {
        return $model->where('id', $id)
            ->with($with)
            ->first();
    }


    /**
     * Get all using where condition
     *
     * @param string $column
     * @param string $where
     * @return Collection
     */
    public function getWhere(string $column, string $where): Collection
    {
        return $this->model
            ->where($column, $where)
            ->get();
    }
}
