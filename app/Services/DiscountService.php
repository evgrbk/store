<?php

namespace App\Services;

use App\Repositories\DiscountRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Exception;

class DiscountService extends Service
{
    /**
     * @var DiscountRepository
     */
    private $discountRepository;

    /**
     * DiscountService constructor.
     *
     * @param DiscountRepository $discountRepository
     */
    public function __construct(DiscountRepository $discountRepository)
    {
        $this->discountRepository = $discountRepository;
    }

    /**
     * Get list of discounts
     *
     * @param int $limit
     * @return LengthAwarePaginator
     */
    public function getList(int $limit): LengthAwarePaginator
    {
        return $this->discountRepository
            ->paginateAllWithPivots($limit);
    }

    /**
     * Create discount
     *
     * @param array $data
     * @return null|Model
     */
    public function storeModel(array $data): ?Model
    {
        try {
            $model = $this->discountRepository
                ->store($data);

            $this->syncData($model, $data);
            return $model;
        } catch (Exception $e) {
            report($e);
            return null;
        }
    }

    /**
     * Update discount
     *
     * @param array $data
     * @param int $id
     * @return null|Model
     */
    public function updateModel(array $data, int $id): ?Model
    {
        try {
            $model = $this->discountRepository
                ->update($data, $id);

            $this->syncData($model, $data);

            return $model;
        } catch (Exception $e) {
            report($e);
            return null;
        }
    }

    /**
     * Delete discount
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->discountRepository
            ->destroy($id);
    }

    /**
     * Sync data
     *
     * @param Model $model
     * @param array $data
     */
    public function syncData(Model $model, array $data)
    {
        if (isset($data['all_countries']) && isset($data['all_brands']) && isset($data['all_categories'])) {
            if (!$data['all_countries']) {
                $model->countries()->sync($data['countries']);
            } else {
                $model->countries()->detach();
            }
            if (!$data['all_brands']) {
                $model->brands()->sync($data['brands']);
            } else {
                $model->brands()->detach();
            }
            if (!$data['all_categories']) {
                $model->categories()->sync($data['categories']);
            } else {
                $model->categories()->detach();
            }
        }

    }
}
