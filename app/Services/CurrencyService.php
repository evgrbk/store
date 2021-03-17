<?php

namespace App\Services;

use App\Repositories\CurrencyRateRepository;
use App\Repositories\CurrencyRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;

class CurrencyService extends Service
{
    /**
     * @var CurrencyRepository
     */
    private $currencyRepository;

    /**
     * @var CurrencyRateRepository
     */
    private $currencyRateRepository;

    /**
     * CurrencyService constructor.
     *
     * @param CurrencyRepository $currencyRepository
     * @param CurrencyRateRepository $currencyRateRepository
     */
    public function __construct(CurrencyRepository $currencyRepository,
                                CurrencyRateRepository $currencyRateRepository)
    {
        $this->currencyRepository = $currencyRepository;
        $this->currencyRateRepository = $currencyRateRepository;
    }

    /**
     * Get list of currencies
     *
     * @param int $limit
     * @return LengthAwarePaginator
     */
    public function getList(int $limit): LengthAwarePaginator
    {
        return $this->currencyRepository
            ->paginateAll($limit);
    }

    /**
     * Create currency
     *
     * @param array $data
     * @return Model
     */
    public function store(array $data): Model
    {
        //Check primary if not exists
        $primary = $this->currencyRepository->getWhere('primary', '1')->first();
        if (!$primary) {
            $data['primary'] = 1;
        } else {
            if ($data['primary']) {
                //Set current primary to false
                $this->currencyRepository
                    ->update(['primary' => false], $primary->id);

                $stored = $this->currencyRepository
                    ->store($data);

                Artisan::call('currencies:rates', ['--no-limit' => true]);

                return $stored;
            }
        }

        return $this->currencyRepository
            ->store($data);
    }

    /**
     * Update currency
     *
     * @param array $data
     * @param int $id
     * @return Model
     */
    public function update(array $data, int $id): Model
    {
        $primary = $this->currencyRepository->getWhere('primary', '1')->first();

        if ($primary) {
            if ($primary->id === $id) {
                //Dont change primary if already true
                $data['primary'] = 1;
            } else if ($data['primary']) {
                //Set current primary to false
                $this->currencyRepository
                    ->update(['primary' => false], $primary->id);

                $updated = $this->currencyRepository
                    ->update($data, $id);

                Artisan::call('currencies:rates', ['--no-limit' => true]);

                return $updated;
            }
        }

        return $this->currencyRepository
            ->update($data, $id);
    }

    /**
     * Delete currency
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        if ($this->currencyRepository->countAll() == 1) {
            return false;
        }

        $primary = $this->currencyRepository->getWhere('primary', '1')->first();

        $result = $this->currencyRepository
            ->destroy($id);

        if ($primary && $primary->id == $id) {
            $this->currencyRepository
                ->update(['primary' => true], $this->currencyRepository->all()->first()->id);

            Artisan::call('currencies:rates', ['--no-limit' => true]);
        }

        return $result;
    }

    /**
     * Get all names
     *
     * @param int $id
     * @return Collection
     */
    public function getNames(): Collection
    {
        return $this->currencyRepository
            ->allColumns(['id', 'name', 'code']);
    }

    /**
     * Get currency by id
     *
     * @param int $id
     */
    public function getCurrencyById(int $id)
    {
        return $this->currencyRepository->getRecord($id) ?? null;
    }

    /**
     * Get base currency code
     *
     * @param int $id
     * @return bool
     */
    public function getBaseCode()
    {
        return optional($this->currencyRepository->getWhere('primary', '1')->first())->code ?? null;
    }
}
