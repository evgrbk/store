<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminPanel\Currency\CurrencyIndexRequest;
use App\Http\Requests\AdminPanel\Currency\CurrencyStoreRequest;
use App\Http\Requests\AdminPanel\Currency\CurrencyUpdateRequest;
use App\Http\Resources\AdminPanel\CurrencyResource;
use App\Services\CurrencyService;
use Illuminate\Http\JsonResponse;

class CurrencyController extends Controller
{
    /**
     * @var CurrencyService
     */
    private CurrencyService $currencyService;

    /**
     * CurrencyController constructor.
     *
     * @param CurrencyService $currencyService
     */
    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    /**
     * Show currencies
     *
     * @param CurrencyIndexRequest $request
     * @return JsonResponse
     */
    public function index(CurrencyIndexRequest $request): JsonResponse
    {
        $this->checkPermission('read-currencies');

        $data = $this->currencyService
            ->getList($request->limit);

        return response()
            ->json([
                'data' => CurrencyResource::collection($data),
                'count' => $data->total()
            ], JsonResponse::HTTP_OK);
    }

    /**
     * Store currency
     *
     * @param CurrencyStoreRequest $request
     * @return JsonResponse
     */
    public function store(CurrencyStoreRequest $request): JsonResponse
    {
        $this->checkPermission('create-currencies');

        $data = $this->currencyService
            ->store($request->validated());

        return response()
            ->json(CurrencyResource::make($data), JsonResponse::HTTP_OK);
    }

    /**
     * Update currency
     *
     * @param CurrencyUpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(CurrencyUpdateRequest $request, $id): JsonResponse
    {
        $this->checkPermission('update-currencies');

        $data = $this->currencyService
            ->update($request->validated(), $id);

        return response()
            ->json(CurrencyResource::make($data), JsonResponse::HTTP_OK);
    }

    /**
     * Delete currency
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $this->checkPermission('delete-currencies');

        if ($this->currencyService->delete($id)) {
            return response()
                ->json([], JsonResponse::HTTP_OK);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Не удалсь удалить эту валюту!',
            ], 422);
        }
    }

    /**
     * All names
     *
     * @return JsonResponse
     */
    public function allNames(): JsonResponse
    {
        $this->checkPermission('read-currencies');

        $data = $this->currencyService
            ->getNames();

        return response()
            ->json($data, JsonResponse::HTTP_OK);
    }
}
