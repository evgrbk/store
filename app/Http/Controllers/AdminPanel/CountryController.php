<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminPanel\Country\CountryIndexRequest;
use App\Http\Requests\AdminPanel\Country\CountryStoreRequest;
use App\Http\Requests\AdminPanel\Country\CountryUpdateRequest;
use App\Http\Resources\AdminPanel\CountryResource;
use App\Http\Resources\AdminPanel\CountryResourceCollection;
use App\Services\CountryService;
use Illuminate\Http\JsonResponse;

class CountryController extends Controller
{
    /**
     * @var CountryService
     */
    private CountryService $countryService;

    /**
     * CountryController constructor.
     *
     * @param CountryService $countryService
     */
    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
    }

    /**
     * Show countries
     *
     * @param CountryIndexRequest $request
     * @return JsonResponse
     */
    public function index(CountryIndexRequest $request): JsonResponse
    {
        $this->checkPermission('read-countries');

        $data = $this->countryService
            ->getList($request->limit);

        return response()
            ->json(CountryResourceCollection::make($data), JsonResponse::HTTP_OK);
    }

    /**
     * Store country
     *
     * @param CountryStoreRequest $request
     * @return JsonResponse
     */
    public function store(CountryStoreRequest $request): JsonResponse
    {
        $this->checkPermission('create-countries');

        $data = $this->countryService
            ->store($request->validated());

        return response()
            ->json(CountryResource::make($data), JsonResponse::HTTP_OK);
    }

    /**
     * Update country
     *
     * @param CountryUpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(CountryUpdateRequest $request, $id): JsonResponse
    {
        $this->checkPermission('update-countries');

        $data = $this->countryService
            ->update($request->validated(), $id);

        return response()
            ->json(CountryResource::make($data), JsonResponse::HTTP_OK);
    }

    /**
     * Delete country
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $this->checkPermission('delete-countries');

        $this->countryService
            ->delete($id);

        return response()
            ->json([], JsonResponse::HTTP_OK);
    }

    /**
     * All names
     *
     * @return JsonResponse
     */
    public function allNames(): JsonResponse
    {
        $this->checkPermission('read-countries');

        $data = $this->countryService
            ->getNames();

        return response()
            ->json($data, JsonResponse::HTTP_OK);
    }
}
