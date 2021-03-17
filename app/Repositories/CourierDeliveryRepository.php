<?php

namespace App\Repositories;

use App\Models\CourierDelivery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class CourierDeliveryRepository extends Repository
{
    /**
     * CourierDeliveryRepository constructor.
     */
    public function __construct()
    {
        $this->model = new CourierDelivery();
    }

    /**
     *  CourierDelivery with country and region
     *
     * @return Builder
     */
    public function withCountryAndRegion(): Builder
    {
        return $this
            ->model
            ->with('country', 'region');
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
}
