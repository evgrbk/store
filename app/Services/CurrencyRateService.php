<?php

namespace App\Services;

use App\Models\CurrencyRate;
use App\Services\ExchangeRatesApiService;
use App\Services\CurrencyService;
use App\Repositories\CurrencyRateRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class CurrencyRateService extends Service
{
    /**
     * @var CurrencyRateRepository
     */
    private $currencyRateRepository;

    /**
     * @var ExchangeRatesApiService
     */
    private ExchangeRatesApiService $exchangeRatesApiService;

    /**
     * @var CurrencyService
     */
    private CurrencyService $currencyService;

    /**
     * CurrencyRateService constructor.
     *
     * @param CurrencyRateRepository $currencyRateRepository
     * @param CurrencyService $currencyService
     * @param ExchangeRatesApiService $exchangeRatesApiService
     */
    public function __construct(
        CurrencyRateRepository $currencyRateRepository,
        CurrencyService $currencyService,
        ExchangeRatesApiService $exchangeRatesApiService)
    {
        $this->currencyRateRepository = $currencyRateRepository;
        $this->currencyService = $currencyService;
        $this->exchangeRatesApiService = $exchangeRatesApiService;
    }

    /**
     * Get list of currency rates
     *
     * @param int $limit
     * @return LengthAwarePaginator
     */
    public function getList(int $limit): LengthAwarePaginator
    {
        $query = $this->currencyRateRepository
            ->withCurrency();

        return $this->currencyRateRepository
            ->getPaginate($query, $limit);
    }

    /**
     * Create currency rate
     *
     * @param array $data
     * @return Model
     */
    public function store(array $data): Model
    {
        if ($data['type'] === CurrencyRate::TYPE_AUTO) {
            $data = $this->updateRate($data);
        }

        $model = $this->currencyRateRepository
            ->store($data);

        return $model;

    }

    /**
     * Update currency rate
     *
     * @param array $data
     * @param int $id
     * @return Model
     */
    public function update(array $data, int $id): Model
    {
        $currentRate = $this->currencyRateRepository->getRecord($id);

        if ($data['type'] === CurrencyRate::TYPE_AUTO) {
            //If changing type from manual to auto
            if ($currentRate->type === CurrencyRate::TYPE_MANUAL) {
                $data = $this->updateRate($data);
            } else if ($currentRate->isAuto()) {
                $dataRate = $this->updateRate($data);
                //Difference between rates
                if (isset($dataRate['rate']) && $dataRate['rate'] != 0) {
                    $diffPercent = abs((1 - $currentRate->rate / $dataRate['rate']) * 100);
                    //Check rate change less limit and difference between dates greater interval
                    if ($diffPercent >= $data['limit'] && (!$currentRate->rate_updated_at || now()->diffInDays($currentRate->rate_updated_at, true) >= $data['interval'])) {
                        $data = $dataRate;
                    }
                }
            }
        }

        return $this->currencyRateRepository
            ->update($data, $id);
    }

    /**
     * Delete currency rate
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->currencyRateRepository
            ->destroy($id);
    }

    /**
     * Update rate in data
     *
     * @param array $data
     * @return array
     */
    public function updateRate(array $data): array
    {
        $base = $this->currencyService->getBaseCode();
        $currencyCode = optional($this->currencyService->getCurrencyById($data['currency_id']))->code;
        if ($base && $currencyCode) {
            if ($base == $currencyCode) {
                $data['rate'] = 1.0000;
                return $data;
            }
            if ($exchangeRates = $this->exchangeRatesApiService->getLatest($base, [$currencyCode])) {
                if (isset($exchangeRates['rates'][$currencyCode])) {
                    $data['rate'] = 1 / $exchangeRates['rates'][$currencyCode];
                }
            }
        }
        return $data;
    }
}
