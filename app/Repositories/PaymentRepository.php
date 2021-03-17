<?php

namespace App\Repositories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PaymentRepository extends Repository
{
    /**
     * PaymentRepository constructor.
     */
    public function __construct()
    {
        $this->model = new Payment();
    }

    /**
     * Get all statuses
     *
     * @return mixed
     */
    public function allStatuses(): array
    {
        return $this->model::STATUSES;
    }

    /**
     * Create new query
     *
     * @return Builder
     */
    public function getOrdersQuery(): Builder
    {
        return $this
            ->model
            ->newQuery();
    }

    /**
     * Get With Relation
     *
     * @param Builder $query
     * @param array $with
     * @return Builder
     */
    public function getOrdersWith(Builder $query, array $with): Builder
    {
        return $query->with($with);
    }

    /**
     * Where status requested
     *
     * @param Builder $query
     * @return Builder
     */
    public function whereRequested(Builder $query): Builder
    {
        return $query->where('status', $this->model::STATUS_REQUESTED);
    }

    /**
     * Execute query
     *
     * @param Builder $query
     * @return Collection
     */
    public function executeQuery(Builder $query): Collection
    {
        return $query->get();
    }

    public function paginate(Builder $query, $limit): LengthAwarePaginator
    {
        return $query->paginate($limit);
    }

    /**
     * Execute query
     *
     * @param Builder $query
     * @return Payment | null
     */
    public function getFirst(Builder $query): ?Payment
    {
        return $query->first();
    }

    /**
     * Set filter by status
     *
     * @param Builder $query
     * @param string $status
     * @return Builder
     */
    public function setFilterByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Set filter by email
     *
     * @param Builder $query
     * @param string $email
     * @return Builder
     */
    public function setFilterByEmail(Builder $query, string $email): Builder
    {
        return $query->where('email', $email);
    }

    /**
     * Set filter by date
     *
     * @param Builder $query
     * @param string $date
     * @return Builder
     */
    public function setFilterByDate(Builder $query, string $date): Builder
    {
        return $query->where('created_at', 'LIKE', "$date%");
    }

    /**
     * Set filter by paymentId
     *
     * @param Builder $query
     * @param int $paymentId
     * @return Builder
     */
    public function setFilterByPayment(Builder $query, int $paymentId): Builder
    {
        return $query->where('payment_service_id', $paymentId);
    }

    /**
     * Set filter by id
     *
     * @param Builder $query
     * @param int $orderIf
     * @return Builder
     */
    public function setFilterByOrderId(Builder $query, int $orderIf): Builder
    {
        return $query->where('id', $orderIf);
    }

    public function setFilterByOrderUuid(Builder $query, int $orderIf): Builder
    {
        return $query->where('uuid', $orderIf);
    }

    public function setFilterByField(Builder $query, string $field, string $orderId): Builder
    {
        return $query->where($field, $orderId);
    }

    public function sortBy(Builder $query, string $field, string $direction = 'asc')
    {
        return $query->orderBy($field, $direction);
    }
}
