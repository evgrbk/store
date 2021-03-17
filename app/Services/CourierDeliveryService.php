<?php

namespace App\Services;

use App\Repositories\CourierDeliveryRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class CourierDeliveryService extends Service
{
    /**
     * @var CourierDeliveryRepository
     */
    private $courierDeliveryRepository;

    /**
     * CourierDeliveryService constructor.
     *
     * @param CourierDeliveryRepository $courierDeliveryRepository
     */
    public function __construct(CourierDeliveryRepository $courierDeliveryRepository)
    {
        $this->courierDeliveryRepository = $courierDeliveryRepository;
    }

    /**
     * Get list of courier deliveries
     *
     * @param int $limit
     * @return LengthAwarePaginator
     */
    public function getList(int $limit): LengthAwarePaginator
    {
        $query = $this->courierDeliveryRepository
            ->withCountryAndRegion();

        return $this->courierDeliveryRepository
            ->getPaginate($query, $limit);
    }

    /**
     * Create courier delivery
     *
     * @param array $data
     * @return Model
     */
    public function store(array $data): Model
    {
        return $this->courierDeliveryRepository
            ->store($data);
    }

    /**
     * Update courier delivery
     *
     * @param array $data
     * @param int $id
     * @return Model
     */
    public function update(array $data, int $id): Model
    {
        return $this->courierDeliveryRepository
            ->update($data, $id);
    }

    /**
     * Delete courier delivery
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->courierDeliveryRepository
            ->destroy($id);
    }
}
