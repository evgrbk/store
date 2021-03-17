<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository extends Repository
{
    public function __construct()
    {
        $this->model = new Order();
    }

    /**
     * Get all records with pagination
     *
     * @param int $limit
     * @param int $customerId
     * @return LengthAwarePaginator
     */
    public function paginateAllWithCustomer(int $limit, int $customerId): LengthAwarePaginator
    {
        return $this
            ->model
            ->where('customer_id', $customerId)
            ->paginate($limit);
    }

    /**
     * Get one order by customer
     *
     * @param int $id
     * @param int $customerId
     * @return Model
     */
    public function getByCustomer(int $id, int $customerId): ?Model
    {
        return $this->model
            ->where('customer_id', $customerId)
            ->where('id', $id)
            ->first();
    }
}
