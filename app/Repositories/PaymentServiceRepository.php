<?php

namespace App\Repositories;

use App\Models\PaymentService;
use Illuminate\Support\Collection;

class PaymentServiceRepository extends Repository
{
    /**
     * PaymentServiceRepository constructor.
     */
    public function __construct()
    {
        $this->model = new PaymentService();
    }

    /**
     * Get payment services list
     *
     * @return Collection
     */
    public function getListOfServices(): Collection
    {
        return collect($this->model::PAYMENT_SERVICES);
    }

    /**
     * Get all services by title
     *
     * @param string $where
     * @return Collection
     */
    public function getWhereServiceTitle(string $where): Collection
    {
        return $this->getWhere('service_title', $where);
    }
}
