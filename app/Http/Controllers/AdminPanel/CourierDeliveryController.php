<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminPanel\CourierDelivery\CourierDeliveryIndexRequest;
use App\Http\Requests\AdminPanel\CourierDelivery\CourierDeliveryStoreRequest;
use App\Http\Requests\AdminPanel\CourierDelivery\CourierDeliveryUpdateRequest;
use App\Http\Resources\AdminPanel\CourierDeliveryResourceCollection;
use App\Http\Resources\AdminPanel\CourierDeliveryResource;
use App\Services\CourierDeliveryService;
use Illuminate\Http\JsonResponse;

class CourierDeliveryController extends Controller
{
    /**
     * @var CourierDeliveryService
     */
    private CourierDeliveryService $courierDeliveryService;

    /**
     * CourierDeliveryController constructor.
     *
     * @param CourierDeliveryService $courierDeliveryService
     */
    public function __construct(CourierDeliveryService $courierDeliveryService)
    {
        $this->courierDeliveryService = $courierDeliveryService;
    }

    /**
     * Show courier delivery
     *
     * @param CourierDeliveryIndexRequest $request
     * @return JsonResponse
     */
    public function index(CourierDeliveryIndexRequest $request): JsonResponse
    {
        $this->checkPermission('read-courier-deliveries');

        $data = $this->courierDeliveryService
            ->getList($request->limit);

        return response()
            ->json(CourierDeliveryResourceCollection::make($data), JsonResponse::HTTP_OK);
    }

    /**
     * Store courier delivery
     *
     * @param CourierDeliveryStoreRequest $request
     * @return JsonResponse
     */
    public function store(CourierDeliveryStoreRequest $request): JsonResponse
    {
        $this->checkPermission('create-courier-deliveries');

        $data = $this->courierDeliveryService
            ->store($request->validated());

        return response()
            ->json(CourierDeliveryResource::make($data), JsonResponse::HTTP_OK);
    }

    /**
     * Update courier delivery
     *
     * @param CourierDeliveryUpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(CourierDeliveryUpdateRequest $request, $id): JsonResponse
    {
        $this->checkPermission('update-courier-deliveries');

        $data = $this->courierDeliveryService
            ->update($request->validated(), $id);

        return response()
            ->json(CourierDeliveryResource::make($data), JsonResponse::HTTP_OK);
    }

    /**
     * Delete courier delivery
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $this->checkPermission('delete-courier-deliveries');

        $this->courierDeliveryService
            ->delete($id);

        return response()
            ->json([], JsonResponse::HTTP_OK);
    }
}
