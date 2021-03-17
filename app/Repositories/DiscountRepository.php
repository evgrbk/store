<?php

namespace App\Repositories;

use App\Models\Discount;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DiscountRepository extends Repository
{
    /**
     * DiscountRepository constructor.
     */
    public function __construct()
    {
        $this->model = new Discount();
    }

    /**
     * Get all discounts with pagination
     *
     * @param int $limit
     * @return LengthAwarePaginator
     */
    public function paginateAllWithPivots(int $limit): LengthAwarePaginator
    {
        return $this->model
            ->with('countries', 'brands', 'categories')
            ->paginate($limit);
    }
}
