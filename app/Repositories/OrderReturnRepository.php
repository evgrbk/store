<?php

namespace App\Repositories;

use App\Models\OrderReturn;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class OrderReturnRepository extends Repository
{
    public function __construct()
    {
        $this->model = new OrderReturn();
    }

    /**
     * Get all order returns with pagination and filters
     *
     * @param int $limit
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function paginateAllWithFilters(int $limit, array $params): LengthAwarePaginator
    {
        $query = $this
            ->model
            ->with('order');

        if (isset($params['query'])) {
            $query = $query->where(function ($query) use ($params) {
                $query->where('order_id', '=', $params['query'])
                    ->orWhereRaw("DATE_FORMAT(created_at, '%d.%m.%Y %H:%i:%s') like ?", ['%' . $params['query'] . '%'])
                    ->orWhereHas('order', function ($query) use ($params) {
                        $query->where('full_name', 'LIKE', '%' . $params['query'] . '%')
                            ->orWhere('phone', 'LIKE', '%' . $params['query'] . '%')
                            ->orWhere('email', 'LIKE', '%' . $params['query'] . '%');
                    });
            });
        }

        if (($filterStatus = Arr::get($params, 'filter_status', 'all')) != 'all') {
            $query = $query->where('status', $filterStatus);
        }

        if (isset($params['orderBy']) && isset($params['ascending'])) {
            if (in_array($params['orderBy'], ['full_name', 'email', 'phone'])) {
                $query->join('orders', 'orders.id', '=', 'order_returns.order_id')
                    ->orderBy('orders.' . $params['orderBy'], $params['ascending'] ? 'ASC' : 'DESC')
                    ->select('order_returns.*', 'orders.' . $params['orderBy']);
            } else {
                $query = $query->orderBy($params['orderBy'], $params['ascending'] ? 'ASC' : 'DESC');
            }
        };

        return $query->paginate($limit);
    }
}
