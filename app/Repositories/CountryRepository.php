<?php

namespace App\Repositories;

use App\Models\Country;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CountryRepository extends Repository
{
    /**
     * Create new model
     *
     * CategoryRepository constructor.
     */
    public function __construct()
    {
        $this->model = new Country();
    }

    public function withRelations()
    {
        return $this
            ->model
            ->with('currency', 'language');
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
