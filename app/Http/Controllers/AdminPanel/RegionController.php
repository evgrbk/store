<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminPanel\Region\RegionIndexRequest;
use App\Http\Requests\AdminPanel\Region\RegionStoreRequest;
use App\Http\Requests\AdminPanel\Region\RegionUpdateRequest;
use App\Http\Resources\AdminPanel\RegionResourceCollection;
use App\Http\Resources\AdminPanel\RegionResource;
use App\Services\RegionService;
use Illuminate\Http\JsonResponse;

class RegionController extends Controller
{
    /**
     * @var RegionService
     */
    private RegionService $regionService;

    /**
     * RegionController constructor.
     *
     * @param RegionService $regionService
     */
    public function __construct(RegionService $regionService)
    {
        $this->regionService = $regionService;
    }

    /**
     * Show regions
     *
     * @param RegionIndexRequest $request
     * @return JsonResponse
     */
    public function index(RegionIndexRequest $request): JsonResponse
    {
        $this->checkPermission('read-regions');

        $data = $this->regionService
            ->getList($request->limit);

        return response()
            ->json(RegionResourceCollection::make($data), JsonResponse::HTTP_OK);
    }

    /**
     * Store region
     *
     * @param RegionStoreRequest $request
     * @return JsonResponse
     */
    public function store(RegionStoreRequest $request): JsonResponse
    {
        $this->checkPermission('create-regions');

        $data = $this->regionService
            ->store($request->validated());

        return response()
            ->json(RegionResource::make($data), JsonResponse::HTTP_OK);
    }

    /**
     * Update region
     *
     * @param RegionUpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(RegionUpdateRequest $request, $id): JsonResponse
    {
        $this->checkPermission('update-regions');

        $data = $this->regionService
            ->update($request->validated(), $id);

        return response()
            ->json(RegionResource::make($data), JsonResponse::HTTP_OK);
    }

    /**
     * Delete region
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $this->checkPermission('delete-regions');

        $this->regionService
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
        $this->checkPermission('read-regions');

        $data = $this->regionService
            ->getNames();

        return response()
            ->json($data, JsonResponse::HTTP_OK);
    }
}
