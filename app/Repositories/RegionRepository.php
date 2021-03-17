<?php

namespace App\Repositories;

use App\Models\Region;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RegionRepository extends Repository
{
    public function __construct()
    {
        $this->model = new Region();
    }

    public function withCountry()
    {
        return $this
            ->model
            ->with('country');
    }

    /**
     * Get paginate
     *
     * @param $query
     * @param $limit
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPaginate($query, $limit): LengthAwarePaginator
    {
        return $query->paginate($limit);
    }
}
