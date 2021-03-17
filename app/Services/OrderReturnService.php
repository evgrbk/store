<?php

namespace App\Services;

use App\Repositories\OrderReturnRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderReturnService extends Service
{
    /**
     * @var OrderReturnRepository
     */
    private $orderReturnRepository;

    /**
     * OrderReturnService constructor.
     *
     * @param OrderReturnRepository $orderReturnRepository
     */
    public function __construct(OrderReturnRepository $orderReturnRepository)
    {
        $this->orderReturnRepository = $orderReturnRepository;
    }

    /**
     * Get order returns
     *
     * @param int $limit
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function getList(int $limit, array $params = []): LengthAwarePaginator
    {
        return $this->orderReturnRepository
            ->paginateAllWithFilters($limit, $params);
    }

    /**
     * Get order return
     *
     * @param int $id
     * @return Model
     */
    public function getOne(int $id): Model
    {
        return $this->orderReturnRepository
            ->getRecord($id);
    }

    /**
     * Create order return
     *
     * @param array $data
     * @return Model
     */
    public function store(array $data): Model
    {
        return $this->orderReturnRepository
            ->store($data);
    }

    /**
     * Update order return
     *
     * @param array $data
     * @param int $id
     * @return Model
     */
    public function update(array $data, int $id): Model
    {
        return $this->orderReturnRepository
            ->update($data, $id);
    }

    /**
     * Delete order return
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->orderReturnRepository
            ->destroy($id);
    }
}
