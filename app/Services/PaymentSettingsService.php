<?php

namespace App\Services;

use App\Models\PaymentService as PaymentServiceModel;
use App\Repositories\PaymentServiceRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PaymentSettingsService
{
    /**
     * QiwiService constructor.
     * @param PaymentServiceRepository $paymentServiceRepository
     */
    public function __construct(    
        PaymentServiceRepository $paymentServiceRepository
     
    ) {
        $this->paymentServiceRepository = $paymentServiceRepository;
    }

     /**
     * Get list of payment service settings
     *
     * @param int $limit
     * @return LengthAwarePaginator
     */
    public function getPaymentSettingsList(int $limit): LengthAwarePaginator
    {
        return $this
            ->paymentServiceRepository
            ->paginateAll($limit);
    }

    /**
     * Get one record from database
     *
     * @param int $id
     * @return PaymentServiceModel
     */
    public function getOneRecord(int $id): PaymentServiceModel
    {
        return $this
            ->paymentServiceRepository
            ->getRecord($id);
    }


    /**
     * Create new payment service setting
     *
     * @param array $data
     * @return Model
     */
    public function createPaymentServiceSetting(array $data): ?Model
    {
        $settings = $this
           ->paymentServiceRepository
           ->store($data);

         return $settings;
    }

    /**
     * Update one record by id
     *
     * @param int $id
     * @param array $data
     * @return Model
     */
    public function updatePaymentSetting(int $id, array $data): ?Model
    {
        $data = $this
            ->paymentServiceRepository
            ->update($data, $id);

        return $data;  
   
    }

     /**
     * Destroy one record by id
     *
     * @param int $id
     * @return bool
     */
    public function destroyOnePaymentSetting(int $id): bool
    {
        return $this
            ->paymentServiceRepository
            ->destroy($id);
    }

    /**
     * Return list of services
     *
     * @return Collection
     */
    public function getListOfServices(): Collection
    {
        $data = $this->paymentServiceRepository
            ->getListOfServices();

        return $data;
    }

}