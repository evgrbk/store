<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminPanel\CurrencyRate\CurrencyRateIndexRequest;
use App\Http\Requests\AdminPanel\CurrencyRate\CurrencyRateStoreRequest;
use App\Http\Requests\AdminPanel\CurrencyRate\CurrencyRateUpdateRequest;
use App\Http\Resources\AdminPanel\CurrencyRateResource;
use App\Http\Resources\AdminPanel\CurrencyRateResourceCollection;
use App\Services\CurrencyRateService;
use Illuminate\Http\JsonResponse;

class CurrencyRateController extends Controller
{
    /**
     * @var CurrencyRateService
     */
    private CurrencyRateService $currencyRateService;

    /**
     * CurrencyController constructor.
     *
     * @param CurrencyRateService $currencyService
     */
    public function __construct(CurrencyRateService $currencyRateService)
    {
        $this->currencyRateService = $currencyRateService;
    }

    /**
     * Show currency rates
     *
     * @param CurrencyRateIndexRequest $request
     * @return JsonResponse
     */
    public function index(CurrencyRateIndexRequest $request): JsonResponse
    {
        $this->checkPermission('read-currency-rates');

        $data = $this->currencyRateService
            ->getList($request->limit);

        return response()
            ->json(CurrencyRateResourceCollection::make($data), JsonResponse::HTTP_OK);
    }

    /**
     * Store currency rate
     *
     * @param CurrencyRateStoreRequest $request
     * @return JsonResponse
     */
    public function store(CurrencyRateStoreRequest $request): JsonResponse
    {
        $this->checkPermission('create-currency-rates');

        $data = $this->currencyRateService
            ->store($request->validated());

        return response()
            ->json(CurrencyRateResource::make($data), JsonResponse::HTTP_OK);
    }

    /**
     * Update currency rate
     *
     * @param CurrencyRateUpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(CurrencyRateUpdateRequest $request, $id): JsonResponse
    {
        $this->checkPermission('update-currency-rates');

        $data = $this->currencyRateService
            ->update($request->validated(), $id);

        return response()
            ->json(CurrencyRateResource::make($data), JsonResponse::HTTP_OK);
    }

    /**
     * Delete currency rate
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $this->checkPermission('delete-currency-rates');

        $this->currencyRateService
            ->delete($id);

        return response()
            ->json('', JsonResponse::HTTP_OK);
    }
}
