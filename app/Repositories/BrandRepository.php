<?php

namespace App\Repositories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class BrandRepository extends Repository
{
    public function __construct()
    {
        $this->model = new Brand();
    }

    /**
     * Find by name
     *
     * @param array $data
     * @return Collection
     */
    public function findByName(array $data): Collection
    {
        $model = $this->model
            ->where('name', 'like', '%' . $data['name'] . '%')->get();

        return $model;
    }

    /**
     * Get limit
     *
     * @param int $limit
     * @return Collection
     */
    public function getLimit(int $limit): Collection
    {
        $model = $this->model->limit(10)->get();

        return $model;
    }

}
