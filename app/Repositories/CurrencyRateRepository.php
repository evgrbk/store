<?php

namespace App\Repositories;

use App\Models\CurrencyRate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class CurrencyRateRepository extends Repository
{
    /**
     * CurrencyRateRepository constructor.
     */
    public function __construct()
    {
        $this->model = new CurrencyRate();
    }

    public function withCurrency()
    {
        return $this
            ->model
            ->with('currency');
    }

    /**
     * Get paginate
     *
     * @param $query
     * @param $limit
     * @return LengthAwarePaginator
     */
    public function getPaginate($query, $limit): LengthAwarePaginator
    {
        return $query->paginate($limit);
    }

    /**
     * Update record
     *
     * @param array $data
     * @param $id
     * @return Model
     */
    public function updateWithoutTimestamps(array $data, int $id): Model
    {
        $model = $this->model
            ->find($id);

        $model->withoutTimestamps()->update($data);

        return $model;
    }
}
