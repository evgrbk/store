<?php

namespace App\Services;

use App\Repositories\PaymentRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class StatisticsOrderService
{
    /**
     * @var PaymentRepository
     */
    public PaymentRepository $paymentRepository;

    /**
     * StatisticsOrderService constructor.
     *
     * @param PaymentRepository $paymentRepository
     */
    public function __construct(
        PaymentRepository $paymentRepository
    ) {
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * Get all statuses
     *
     * @return array
     */
    public function getAllOrderStatuses(): array
    {
        return $this
            ->paymentRepository
            ->allStatuses();
    }

    /**
     * Get all goods with relations
     *
     * @param array $filter
     * @return Collection
     */
    public function getAllOrdersWith(array $filter): LengthAwarePaginator
    {
        $query = $this
            ->paymentRepository
            ->getOrdersQuery();

        if (isset($filter['status'])) {
            $query = $this
                ->paymentRepository
                ->setFilterByStatus($query, $filter['status']);
        }

        if (isset($filter['email'])) {
            $query = $this
                ->paymentRepository
                ->setFilterByEmail($query, $filter['email']);
        }

        if (isset($filter['date'])) {
            $query = $this
                ->paymentRepository
                ->setFilterByDate($query, $filter['date']);
        }

        if (isset($filter['payment_id'])) {
            $query = $this
                ->paymentRepository
                ->setFilterByPayment($query, $filter['payment_id']);
        }

        if (isset($filter['order_id'])) {
            $query = $this
                ->paymentRepository
                ->setFilterByOrderId($query, $filter['order_id']);
        }

        $query = $this
            ->paymentRepository
            ->getOrdersWith($query, ['payment', 'goods']);

        $query = $this
            ->paymentRepository
            ->sortBy($query, 'created_at', 'desc');

        $query = $this
            ->paymentRepository
            ->paginate($query, $filter['limit']);

        return $query;
    }
}
