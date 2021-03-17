<?php

namespace App\Repositories;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class CustomerRepository extends Repository
{
    public function __construct()
    {
        $this->model = new Customer();
    }

    /**
     * Get all goods with pagination
     *
     * @param int $limit
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function paginateAllWithFilters(int $limit, array $params): LengthAwarePaginator
    {
        $query = $this
            ->model;

        if (isset($params['query'])) {
            $query = $query->where(function ($query) use ($params) {
                $query->where('full_name', 'like', '%' . $params['query'] . '%')
                    ->orWhere('email', 'LIKE', '%' . $params['query'] . '%')
                    ->orWhere('phone', 'LIKE', '%' . $params['query'] . '%');
            });
        }

        if (isset($params['filter_active'])) {
            if ($params['filter_active'] != 'all') {
                $query = $query->where('is_active', filter_var($params['filter_active'], FILTER_VALIDATE_BOOLEAN));
            }
        }

        if (isset($params['orderBy']) && isset($params['ascending'])) {
            $query = $query->orderBy($params['orderBy'], $params['ascending'] ? 'ASC' : 'DESC');
        };

        return $query->paginate($limit);
    }
}
